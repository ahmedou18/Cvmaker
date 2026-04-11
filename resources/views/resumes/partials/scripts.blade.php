@props([
    'isEdit' => false,
    'nameLocked' => false,
    'nameChangesLeft' => null,
])

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    function resumeForm(initialData = {}) {
        console.log('Initial data received:', initialData);
        
        return {
            // === الحالة الأساسية ===
            step: 1,
            maxStep: 6,
            aiCredits: {{ auth()->user()->ai_credits_balance ?? 0 }},
            showPlansModal: false,
            currentLang: "{{ session('resume_language', 'ar') }}",
            stepLabels: ['المعلومات الشخصية', 'المؤهلات الأكاديمية', 'الخبرات المهنية', 'المهارات والملخص', 'اللغات', 'اللمسات الأخيرة'],
            
            // === البيانات (تؤخذ من المعامل initialData) ===
            full_name: initialData.full_name || '',
            job_title: initialData.job_title || '',
            email: initialData.email || '',
            phone: initialData.phone || '',
            address: initialData.address || '',
            summary: initialData.summary || '',
            skills: initialData.skills || '',
            educations: initialData.educations?.length ? initialData.educations : [{ id: Date.now(), institution: '', degree: '', field_of_study: '', graduation_year: '' }],
            experiences: initialData.experiences?.length ? initialData.experiences : [{ id: Date.now(), company: '', position: '', start_date: '', end_date: '', is_current: false, description: '' }],
            languages: initialData.languages?.length ? initialData.languages : [{ id: Date.now(), name: '', proficiency: 'متوسط' }],
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

            addLanguage() {
                if (this.languages.length && !this.languages[this.languages.length-1].name.trim()) {
                    alert('يرجى كتابة اسم اللغة أولاً'); return;
                }
                this.languages.push({id: Date.now(), name: '', proficiency: 'متوسط'});
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

            // دالة مشتركة لاستدعاء الـ AI
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

            async generateExperienceAI(index) {
    const exp = this.experiences[index];
    const company = exp.company?.trim() || '';
    const position = exp.position?.trim() || '';
    if (!position) return alert('يرجى كتابة المسمى الوظيفي أولاً');
    if (!company) return alert('يرجى كتابة اسم الشركة أولاً (لتحسين دقة التوليد)');
    
    let context = `المسمى الوظيفي: ${position}\nالشركة: ${company}`;
    if (exp.start_date) {
        context += `\nتاريخ البداية: ${exp.start_date}`;
        if (exp.end_date) context += `\nتاريخ النهاية: ${exp.end_date}`;
        else if (exp.is_current) context += `\nتاريخ النهاية: حتى الآن`;
    }
    
    this.experiences[index].description = "⏳ جاري التوليد بناءً على الشركة والمسمى والتواريخ...";
    const result = await this.callAiApi('description', context);
    this.experiences[index].description = result || "";
},

            async generateSkillsAI() {
    // بناء سياق غني من البيانات المتاحة
    let contextParts = [];
    
    // 1. المسمى الوظيفي (إجباري)
    if (!this.job_title) return alert('أدخل المسمى الوظيفي أولاً');
    contextParts.push(`المسمى الوظيفي: ${this.job_title}`);
    
    // 2. الخبرات العملية (أهم المهام)
    if (this.experiences.length > 0) {
        const expSummary = this.experiences
            .filter(exp => exp.position && exp.company)
            .map(exp => `${exp.position} في ${exp.company}${exp.description ? ` (المهام: ${exp.description.substring(0, 100)})` : ''}`)
            .join('؛ ');
        if (expSummary) contextParts.push(`الخبرات: ${expSummary}`);
    }
    
    // 3. المؤهلات التعليمية (التخصص والمؤسسة)
    if (this.educations.length > 0) {
        const eduSummary = this.educations
            .filter(edu => edu.degree || edu.field_of_study)
            .map(edu => `${edu.degree || ''} ${edu.field_of_study ? `في ${edu.field_of_study}` : ''} من ${edu.institution || ''}`.trim())
            .filter(s => s.length > 0)
            .join('؛ ');
        if (eduSummary) contextParts.push(`التعليم: ${eduSummary}`);
    }
    
    // 4. الأقسام الإضافية (شهادات، دورات، مشاريع)
    if (this.extra_sections && this.extra_sections.length > 0) {
        const extraSummary = this.extra_sections
            .map(section => `${section.title}: ${section.content.substring(0, 150)}`)
            .join('؛ ');
        if (extraSummary) contextParts.push(`معلومات إضافية: ${extraSummary}`);
    }
    
    const fullContext = contextParts.join('\n');
    
    this.skills = "⏳ جاري تحليل بياناتك واقتراح المهارات المناسبة...";
    const result = await this.callAiApi('skills', fullContext);
    if (result) this.skills = result;
},

           async generateSummaryAI() {
    if (!this.job_title) return alert('أدخل المسمى الوظيفي أولاً');
    
    // بناء سياق غني من جميع بيانات السيرة
    let contextParts = [];
    
    // 1. المسمى الوظيفي (إجباري)
    contextParts.push(`المسمى الوظيفي المستهدف: ${this.job_title}`);
    
    // 2. الخبرات العملية (الشركات والمهام)
    if (this.experiences.length > 0) {
        const expSummary = this.experiences
            .filter(exp => exp.position && exp.company)
            .map(exp => `${exp.position} في ${exp.company}${exp.description ? ` (${exp.description.substring(0, 150)})` : ''}`)
            .join('؛ ');
        if (expSummary) contextParts.push(`الخبرات: ${expSummary}`);
    }
    
    // 3. المؤهلات التعليمية
    if (this.educations.length > 0) {
        const eduSummary = this.educations
            .filter(edu => edu.degree || edu.field_of_study)
            .map(edu => `${edu.degree || ''} ${edu.field_of_study ? `في ${edu.field_of_study}` : ''} من ${edu.institution || ''}`.trim())
            .filter(s => s.length > 0)
            .join('؛ ');
        if (eduSummary) contextParts.push(`التعليم: ${eduSummary}`);
    }
    
    // 4. المهارات
    if (this.skills && this.skills.trim()) {
        contextParts.push(`المهارات: ${this.skills.substring(0, 200)}`);
    }
    
    // 5. اللغات
    if (this.languages.length > 0) {
        const langSummary = this.languages
            .filter(lang => lang.name)
            .map(lang => `${lang.name} (${lang.proficiency})`)
            .join('، ');
        if (langSummary) contextParts.push(`اللغات: ${langSummary}`);
    }
    
    // 6. الأقسام الإضافية (مشاريع، شهادات)
    if (this.extra_sections && this.extra_sections.length > 0) {
        const extraSummary = this.extra_sections
            .map(section => `${section.title}: ${section.content.substring(0, 100)}`)
            .join('؛ ');
        if (extraSummary) contextParts.push(`إضافات: ${extraSummary}`);
    }
    
    const fullContext = contextParts.join('\n');
    
    this.summary = "⏳ جاري تحليل بياناتك وكتابة ملخص مهني شامل...";
    const result = await this.callAiApi('summary', fullContext);
    if (result) this.summary = result;
},

async reviewEntireResumeAI() {
    if (this.aiCredits <= 0) { this.showPlansModal = true; return; }
    this.isReviewing = true;
    try {
        const payload = {
            lang: this.currentLang,
            job_title: this.job_title,
            summary: this.summary,
            skills: this.skills,
            educations: this.educations,
            experiences: this.experiences,
            languages: this.languages,
            extra_sections: this.extra_sections
        };
        const res = await fetch("{{ route('ai.review') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error);
        if (data.success && data.data) {
            const imp = data.data;
            if (imp.job_title) this.job_title = imp.job_title;
            if (imp.summary) this.summary = imp.summary;
            if (imp.skills) this.skills = imp.skills;
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
            this.reviewSuccessMessage = 'تمت مراجعة الأجزاء المهنية وتحسينها بنجاح!';
            setTimeout(() => this.reviewSuccessMessage = '', 5000);
        }
    } catch (err) {
        alert('فشل في مراجعة السيرة: ' + err.message);
    } finally {
        this.isReviewing = false;
    }
},

            async uploadAndParseCV(e) {
                const file = e.target.files[0];
                if (!file || this.aiCredits <= 0) return;
                this.isUploading = true;
                const fd = new FormData();
                fd.append('cv_file', file);
                try {
                    // تم تصحيح المسار ليتوافق مع web.php
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
                        if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                        alert('تم استخراج البيانات بنجاح!');
                    }
                } catch (err) { alert('فشل معالجة الملف'); }
                finally { this.isUploading = false; e.target.value = ''; }
            },

            // منطق القص (Cropper) المصلح
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
                    alert('⚠️ تحذير: هذا آخر تغيير مسموح به للاسم. بعد الحفظ، لن تتمكن من تعديله مرة أخرى.');
                }
            }
        };
    }
    </script>