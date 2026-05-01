@props([
    'isEdit' => false,
    'nameLocked' => false,
    'nameChangesLeft' => null,
    'currentLang' => 'ar',
])

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    // تعريف الترجمات المستخدمة في JavaScript (يتم تمريرها من الصفحة الرئيسية)
    window.translations = window.translations || {};

    function resumeForm(initialData = {}) {
        console.log('Initial data received:', initialData);
        
        return {
            // === الحالة الأساسية ===
            step: 1,
            maxStep: 8, // 8 خطوات: شخصية، تعليم، خبرات، مهارات+ملخص، لغات، هوايات، مراجع، اللمسات الأخيرة
            aiCredits: {{ auth()->user()->ai_credits_balance ?? 0 }},
            showPlansModal: false,
            currentLang: "{{ $currentLang }}",
            stepLabels: [
                window.translations.stepPersonal || "المعلومات الشخصية",
                window.translations.stepEducation || "المؤهلات الأكاديمية",
                window.translations.stepExperience || "الخبرات المهنية",
                window.translations.stepSkillsSummary || "المهارات والملخص",
                window.translations.stepLanguages || "اللغات",
                "الهوايات",
                "المراجع",
                window.translations.stepFinal || "اللمسات الأخيرة"
            ],
            
            // === البيانات ===
            full_name: initialData.full_name || '',
            job_title: initialData.job_title || '',
            email: initialData.email || '',
            phone: initialData.phone || '',
            address: initialData.address || '',
            summary: initialData.summary || '',
            skills: initialData.skills || '', // النص القديم (للتوافق)
            skillsArray: initialData.skillsArray || [{ id: Date.now(), name: '', percentage: 80 }],
            educations: initialData.educations?.length ? initialData.educations : [{ id: Date.now(), institution: '', degree: '', field_of_study: '', graduation_year: '' }],
            experiences: initialData.experiences?.length ? initialData.experiences : [{ id: Date.now(), company: '', position: '', start_date: '', end_date: '', is_current: false, description: '' }],
            languages: initialData.languages?.length ? initialData.languages : [{ id: Date.now(), name: '', proficiency: window.translations.intermediate || 'متوسط', level: 3 }],
            hobbiesArray: initialData.hobbies || [],
            referencesArray: initialData.references || [],
            extra_sections: initialData.extra_sections || [],
            existingPhoto: initialData.existingPhoto || null,
            
            // === حالات الواجهة ===
            isUploading: false,
            isReviewing: false,
            reviewSuccessMessage: '',
            showCropperModal: false,
            cropper: null,
            croppedPhotoData: null,
            shape: 'square',
            
            // === إعدادات التعديل ===
            isEditMode: {{ $isEdit ? 'true' : 'false' }},
            nameLocked: {{ $nameLocked ? 'true' : 'false' }},
            nameChangesLeft: {{ $nameChangesLeft ?? 'null' }},

            // خاصية محسوبة لتحويل skillsArray إلى نص مفصول بفواصل (لـ AI والتوافق القديم)
            get skillsTextForAI() {
                return this.skillsArray.map(s => s.name).filter(n => n).join(', ');
            },

            // دالة مساعدة لتحديث حقل skills النصي بعد تغيير المهارات
            updateSkillsText() {
                this.skills = this.skillsArray.map(s => s.name).filter(n => n).join(', ');
            },

            // --- دوال إضافة وحذف العناصر الأساسية (موجودة) ---
            addLanguage() {
                if (this.languages.length && !this.languages[this.languages.length-1].name.trim()) {
                    alert(window.translations.alertEnterLanguageFirst || 'يرجى كتابة اسم اللغة أولاً');
                    return;
                }
                this.languages.push({
                    id: Date.now(),
                    name: '',
                    proficiency: window.translations.intermediate || 'متوسط',
                    level: 3
                });
            },

            addEducation() {
                this.educations.push({
                    id: Date.now(),
                    institution: '',
                    degree: '',
                    field_of_study: '',
                    graduation_year: ''
                });
            },

            addExperience() {
                this.experiences.push({
                    id: Date.now(),
                    company: '',
                    position: '',
                    start_date: '',
                    end_date: '',
                    is_current: false,
                    description: ''
                });
            },

            addHobby() {
                this.hobbiesArray.push({
                    id: Date.now(),
                    name: '',
                    icon: '',
                    description: ''
                });
            },

            addReference() {
                this.referencesArray.push({
                    id: Date.now(),
                    full_name: '',
                    job_title: '',
                    company: '',
                    email: '',
                    phone: '',
                    notes: ''
                });
            },

            // --- دوال إدارة extra_sections ---
            addExtraSection() {
                this.extra_sections.push({ title: '', content: '' });
            },
            removeExtraSection(index) {
                this.extra_sections.splice(index, 1);
            },
            moveExtraSectionUp(index) {
                if (index > 0) {
                    [this.extra_sections[index-1], this.extra_sections[index]] = [this.extra_sections[index], this.extra_sections[index-1]];
                }
            },
            moveExtraSectionDown(index) {
                if (index < this.extra_sections.length - 1) {
                    [this.extra_sections[index+1], this.extra_sections[index]] = [this.extra_sections[index], this.extra_sections[index+1]];
                }
            },

            // --- استدعاء API المعياري (موجود) ---
            async callAiApi(type, context) {
                if (this.aiCredits <= 0) { this.showPlansModal = true; return null; }
                try {
                    const res = await fetch("{{ route('ai.generate') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ type: type, context: context, lang: this.currentLang })
                    });
                    const data = await res.json();
                    if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                    return data.result;
                } catch (e) { return null; }
            },

            // --- توليد المهارات (مُحسَّن لاستقبال JSON الحقيقي) ---
            async generateSkillsAI() {
                if (!this.job_title) return alert(window.translations.alertEnterJobTitleFirstSkills || 'أدخل المسمى الوظيفي أولاً');
                
                let contextParts = [];
                contextParts.push(`المسمى الوظيفي: ${this.job_title}`);
                
                if (this.experiences.length > 0) {
                    const expSummary = this.experiences
                        .filter(exp => exp.position && exp.company)
                        .map(exp => `${exp.position} في ${exp.company}${exp.description ? ` (المهام: ${exp.description.substring(0, 100)})` : ''}`)
                        .join('؛ ');
                    if (expSummary) contextParts.push(`الخبرات: ${expSummary}`);
                }
                
                if (this.educations.length > 0) {
                    const eduSummary = this.educations
                        .filter(edu => edu.degree || edu.field_of_study)
                        .map(edu => `${edu.degree || ''} ${edu.field_of_study ? `في ${edu.field_of_study}` : ''} من ${edu.institution || ''}`.trim())
                        .filter(s => s.length > 0)
                        .join('؛ ');
                    if (eduSummary) contextParts.push(`التعليم: ${eduSummary}`);
                }
                
                if (this.extra_sections && this.extra_sections.length > 0) {
                    const extraSummary = this.extra_sections
                        .map(section => `${section.title}: ${section.content.substring(0, 150)}`)
                        .join('؛ ');
                    if (extraSummary) contextParts.push(`معلومات إضافية: ${extraSummary}`);
                }
                
                const fullContext = contextParts.join('\n');
                this.skills = window.translations.aiAnalyzingSkills || '⏳ جاري تحليل بياناتك واقتراح المهارات المناسبة...';
                const result = await this.callAiApi('skills', fullContext);
                if (result) {
                    try {
                        // *** التعديل الأساسي: result هو JSON يحتوي على مصفوفة كائنات ***
                        let skillsArray = JSON.parse(result);
                        if (Array.isArray(skillsArray)) {
                            // نأخذ أول 5 عناصر فقط، ونتأكد من وجود name و percentage
                            this.skillsArray = skillsArray.slice(0, 5).map((s, idx) => ({
                                id: Date.now() + Math.random() + idx,
                                name: s.name || 'مهارة',
                                percentage: s.percentage || 80
                            }));
                        } else {
                            // fallback: لو لم تكن مصفوفة، نتعامل معه كنص مفصول بفواصل
                            const skillsNames = result.split(',').map(s => s.trim()).filter(s => s && !s.match(/\{/));
                            this.skillsArray = skillsNames.slice(0, 5).map((name, idx) => ({
                                id: Date.now() + Math.random() + idx,
                                name: name,
                                percentage: 80
                            }));
                        }
                        this.updateSkillsText();
                    } catch (e) {
                        // fallback عند فشل تحليل JSON (حالة نادرة)
                        console.error('Failed parsing skills JSON, using text fallback', e);
                        const skillsNames = result.split(',').map(s => s.trim()).filter(s => s && !s.match(/\{/));
                        this.skillsArray = skillsNames.slice(0, 5).map((name, idx) => ({
                            id: Date.now() + Math.random() + idx,
                            name: name,
                            percentage: 80
                        }));
                        this.updateSkillsText();
                    }
                }
            },

            // --- توليد وصف خبرة (بدون تغيير) ---
            async generateExperienceAI(index) {
                const exp = this.experiences[index];
                const company = exp.company?.trim() || '';
                const position = exp.position?.trim() || '';
                if (!position) return alert(window.translations.alertEnterJobTitleFirst || 'يرجى كتابة المسمى الوظيفي أولاً');
                if (!company) return alert(window.translations.alertEnterCompanyFirst || 'يرجى كتابة اسم الشركة أولاً (لتحسين دقة التوليد)');
                
                let context = `المسمى الوظيفي: ${position}\nالشركة: ${company}`;
                if (exp.start_date) {
                    context += `\nتاريخ البداية: ${exp.start_date}`;
                    if (exp.end_date) context += `\nتاريخ النهاية: ${exp.end_date}`;
                    else if (exp.is_current) context += `\nتاريخ النهاية: حتى الآن`;
                }
                
                this.experiences[index].description = window.translations.aiGenerating || '⏳ جاري التوليد بناءً على الشركة والمسمى والتواريخ...';
                const result = await this.callAiApi('description', context);
                this.experiences[index].description = result || "";
            },

            // --- توليد الملخص (بدون تغيير) ---
            async generateSummaryAI() {
                if (!this.job_title) return alert(window.translations.alertEnterJobTitleFirst || 'أدخل المسمى الوظيفي أولاً');
                
                let contextParts = [];
                contextParts.push(`المسمى الوظيفي المستهدف: ${this.job_title}`);
                
                if (this.experiences.length > 0) {
                    const expSummary = this.experiences
                        .filter(exp => exp.position && exp.company)
                        .map(exp => `${exp.position} في ${exp.company}${exp.description ? ` (${exp.description.substring(0, 150)})` : ''}`)
                        .join('؛ ');
                    if (expSummary) contextParts.push(`الخبرات: ${expSummary}`);
                }
                
                if (this.educations.length > 0) {
                    const eduSummary = this.educations
                        .filter(edu => edu.degree || edu.field_of_study)
                        .map(edu => `${edu.degree || ''} ${edu.field_of_study ? `في ${edu.field_of_study}` : ''} من ${edu.institution || ''}`.trim())
                        .filter(s => s.length > 0)
                        .join('؛ ');
                    if (eduSummary) contextParts.push(`التعليم: ${eduSummary}`);
                }
                
                if (this.skills && this.skills.trim()) {
                    contextParts.push(`المهارات: ${this.skills.substring(0, 200)}`);
                } else if (this.skillsArray.length) {
                    contextParts.push(`المهارات: ${this.skillsArray.map(s => s.name).join(', ').substring(0, 200)}`);
                }
                
                if (this.languages.length > 0) {
                    const langSummary = this.languages
                        .filter(lang => lang.name)
                        .map(lang => `${lang.name} (${lang.proficiency})`)
                        .join('، ');
                    if (langSummary) contextParts.push(`اللغات: ${langSummary}`);
                }
                
                if (this.extra_sections && this.extra_sections.length > 0) {
                    const extraSummary = this.extra_sections
                        .map(section => `${section.title}: ${section.content.substring(0, 100)}`)
                        .join('؛ ');
                    if (extraSummary) contextParts.push(`إضافات: ${extraSummary}`);
                }
                
                const fullContext = contextParts.join('\n');
                this.summary = window.translations.aiWritingSummary || '⏳ جاري تحليل بياناتك وكتابة ملخص مهني شامل...';
                const result = await this.callAiApi('summary', fullContext);
                if (result) this.summary = result;
            },

            // --- مراجعة كاملة (مُحسَّنة للتعامل مع المهارات بصيغ متعددة) ---
            async reviewEntireResumeAI() {
                if (this.aiCredits <= 0) { this.showPlansModal = true; return; }
                this.isReviewing = true;
                try {
                    const skillsText = this.skillsTextForAI;
                    const payload = {
                        lang: this.currentLang,
                        job_title: this.job_title,
                        summary: this.summary,
                        skills: skillsText,
                        educations: this.educations,
                        experiences: this.experiences,
                        languages: this.languages,
                        extra_sections: this.extra_sections
                    };
                    const res = await fetch("{{ route('ai.review') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.error || 'Error');
                    if (data.success && data.data) {
                        const imp = data.data;
                        if (imp.job_title) this.job_title = imp.job_title;
                        if (imp.summary) this.summary = imp.summary;
                        
                        // *** تحديث المهارات بذكاء حسب الصيغة الواردة من السيرفر ***
                        if (imp.skills) {
                            if (Array.isArray(imp.skills)) {
                                // مصفوفة كائنات مثل [{name: "..", percentage: 80}]
                                this.skillsArray = imp.skills.map((s, idx) => ({
                                    id: Date.now() + Math.random() + idx,
                                    name: s.name || s,
                                    percentage: s.percentage || 80
                                }));
                            } else if (typeof imp.skills === 'string') {
                                // نص عادي، قد يكون JSON أحيانًا
                                try {
                                    const parsed = JSON.parse(imp.skills);
                                    if (Array.isArray(parsed)) {
                                        this.skillsArray = parsed.map((s, idx) => ({
                                            id: Date.now() + Math.random() + idx,
                                            name: s.name || s,
                                            percentage: s.percentage || 80
                                        }));
                                    } else {
                                        throw new Error('Not array');
                                    }
                                } catch (e) {
                                    // فشل التحليل، نعتبره أسماء مفصولة بفواصل
                                    const names = imp.skills.split(',').map(s => s.trim()).filter(s => s);
                                    this.skillsArray = names.map((name, idx) => ({
                                        id: Date.now() + Math.random() + idx,
                                        name: name,
                                        percentage: 80
                                    }));
                                }
                            }
                            this.updateSkillsText();
                        }
                        
                        if (imp.experiences?.length) {
                            this.experiences = imp.experiences.map((exp, idx) => ({ ...exp, id: this.experiences[idx]?.id || Date.now() + Math.random() }));
                        }
                        if (imp.educations?.length) {
                            this.educations = imp.educations.map((edu, idx) => ({ ...edu, id: this.educations[idx]?.id || Date.now() + Math.random() }));
                        }
                        if (imp.languages?.length) {
                            this.languages = imp.languages.map((lang, idx) => ({ ...lang, id: this.languages[idx]?.id || Date.now() + Math.random() }));
                        }
                        if (imp.extra_sections) this.extra_sections = imp.extra_sections;
                        
                        if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                        this.reviewSuccessMessage = window.translations.aiReviewSuccess || 'تمت مراجعة الأجزاء المهنية وتحسينها بنجاح!';
                        setTimeout(() => this.reviewSuccessMessage = '', 5000);
                    }
                } catch (err) {
                    alert((window.translations.aiReviewFailed || 'فشل في مراجعة السيرة: ') + err.message);
                } finally {
                    this.isReviewing = false;
                }
            },

            // --- رفع وتحليل السيرة من ملف (بدون تغيير) ---
            async uploadAndParseCV(e) {
                const file = e.target.files[0];
                if (!file || this.aiCredits <= 0) return;
                this.isUploading = true;
                const fd = new FormData();
                fd.append('cv_file', file);
                try {
                    const res = await fetch("{{ route('api.cv.parse') }}", {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        const d = data.data.personal_details || {};
                        this.full_name = d.full_name || '';
                        this.job_title = d.job_title || '';
                        this.email = d.email || '';
                        this.phone = d.phone || '';
                        this.summary = d.summary || '';
                        if (data.data.experiences) this.experiences = data.data.experiences.map(ex => ({...ex, id: Math.random()}));
                        if (data.data.educations) this.educations = data.data.educations.map(ed => ({...ed, id: Math.random()}));
                        if (data.data.skills && data.data.skills.length) {
                            this.skillsArray = data.data.skills.map((name, idx) => ({
                                id: Date.now() + idx,
                                name: name,
                                percentage: 80
                            }));
                            this.updateSkillsText();
                        }
                        if (data.data.extra_sections && data.data.extra_sections.length) {
                            this.extra_sections = data.data.extra_sections;
                        }
                        if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                        alert(window.translations.alertDataExtracted || 'تم استخراج البيانات بنجاح!');
                    }
                } catch (err) { alert(window.translations.alertFileProcessingFailed || 'فشل معالجة الملف'); }
                finally { this.isUploading = false; e.target.value = ''; }
            },

            // --- دوال الصورة والمحصول (بدون تغيير) ---
            initFile(e) {
                const file = e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (event) => {
                    const img = document.getElementById('cropper-image');
                    img.src = event.target.result;
                    this.showCropperModal = true;
                    if (this.cropper) this.cropper.destroy();
                    setTimeout(() => {
                        this.cropper = new Cropper(img, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                        });
                    }, 250);
                };
                reader.readAsDataURL(file);
            },

            saveCroppedImage() {
                if (!this.cropper) return;
                this.croppedPhotoData = this.cropper.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/jpeg');
                this.closeCropper();
            },

            cancelCropping() {
                this.closeCropper();
            },

            closeCropper() {
                this.showCropperModal = false;
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            showNameWarning() {
                if (this.isEditMode && !this.nameLocked && this.nameChangesLeft === 1) {
                    alert(window.translations.alertNameChangeWarning || '⚠️ تحذير: هذا آخر تغيير مسموح به للاسم. بعد الحفظ، لن تتمكن من تعديله مرة أخرى.');
                }
            }
        };
    }
</script>