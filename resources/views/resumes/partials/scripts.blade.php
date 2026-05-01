@props([
    'isEdit' => false,
    'nameLocked' => false,
    'nameChangesLeft' => null,
    'currentLang' => 'ar',
])

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    window.translations = window.translations || {};

    function resumeForm(initialData = {}) {
        console.log('Initial data received:', initialData);
        
        return {
            step: 1,
            maxStep: 8,
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
            
            full_name: initialData.full_name || '',
            job_title: initialData.job_title || '',
            email: initialData.email || '',
            phone: initialData.phone || '',
            address: initialData.address || '',
            summary: initialData.summary || '',
            skills: initialData.skills || '',
            skillsArray: initialData.skillsArray || [{ id: Date.now(), name: '', percentage: 80 }],
            educations: initialData.educations?.length ? initialData.educations : [{ id: Date.now(), institution: '', degree: '', field_of_study: '', graduation_year: '' }],
            experiences: initialData.experiences?.length ? initialData.experiences : [{ id: Date.now(), company: '', position: '', start_date: '', end_date: '', is_current: false, description: '' }],
            languages: initialData.languages?.length ? initialData.languages : [{ id: Date.now(), name: '', proficiency: window.translations.intermediate || 'متوسط', level: 3 }],
            hobbiesArray: initialData.hobbies || [],
            referencesArray: initialData.references || [],
            extra_sections: initialData.extra_sections || [],
            existingPhoto: initialData.existingPhoto || null,
            
            isUploading: false,
            isReviewing: false,
            reviewSuccessMessage: '',
            showCropperModal: false,
            cropper: null,
            croppedPhotoData: null,
            shape: 'square',
            
            isEditMode: {{ $isEdit ? 'true' : 'false' }},
            nameLocked: {{ $nameLocked ? 'true' : 'false' }},
            nameChangesLeft: {{ $nameChangesLeft ?? 'null' }},

            get skillsTextForAI() {
                return this.skillsArray.map(s => s.name).filter(n => n).join(', ');
            },

            updateSkillsText() {
                this.skills = this.skillsArray.map(s => s.name).filter(n => n).join(', ');
            },

            // دوال إضافة العناصر
            addLanguage() {
                if (this.languages.length && !this.languages[this.languages.length-1].name.trim()) {
                    alert(window.translations.alertEnterLanguageFirst || 'يرجى كتابة اسم اللغة أولاً');
                    return;
                }
                this.languages.push({ id: Date.now(), name: '', proficiency: window.translations.intermediate || 'متوسط', level: 3 });
            },

            addEducation() {
                this.educations.push({ id: Date.now(), institution: '', degree: '', field_of_study: '', graduation_year: '' });
            },

            addExperience() {
                this.experiences.push({ id: Date.now(), company: '', position: '', start_date: '', end_date: '', is_current: false, description: '' });
            },

            addHobby() {
                this.hobbiesArray.push({ id: Date.now(), name: '', icon: '', description: '' });
            },

            addReference() {
                this.referencesArray.push({ id: Date.now(), full_name: '', job_title: '', company: '', email: '', phone: '', notes: '' });
            },

            addExtraSection() {
                this.extra_sections.push({ title: '', content: '' });
            },
            removeExtraSection(index) { this.extra_sections.splice(index, 1); },
            moveExtraSectionUp(index) {
                if (index > 0) [this.extra_sections[index-1], this.extra_sections[index]] = [this.extra_sections[index], this.extra_sections[index-1]];
            },
            moveExtraSectionDown(index) {
                if (index < this.extra_sections.length - 1) [this.extra_sections[index+1], this.extra_sections[index]] = [this.extra_sections[index], this.extra_sections[index+1]];
            },

            // ========== استدعاء API الخاص باقتراح المهارات (المسار الجديد) ==========
            async callSuggestSkillsApi(payload) {
                if (this.aiCredits <= 0) { this.showPlansModal = true; return null; }
                try {
                    const res = await fetch("{{ route('ai.suggest-skills') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                    return data.skills; // مصفوفة كائنات {name, percentage}
                } catch (e) { return null; }
            },

            // ========== دالة اقتراح المهارات (تستخدم suggest-skills) ==========
            async generateSkillsAI() {
                if (!this.job_title) return alert(window.translations.alertEnterJobTitleFirstSkills || 'أدخل المسمى الوظيفي أولاً');

                const payload = {
                    job_title: this.job_title,
                    experiences: this.experiences,
                    educations: this.educations,
                    lang: this.currentLang
                };

                this.skills = window.translations.aiAnalyzingSkills || '⏳ جاري تحليل بياناتك واقتراح المهارات المناسبة...';
                const skills = await this.callSuggestSkillsApi(payload);
                if (skills && Array.isArray(skills)) {
                    const filtered = skills
                        .filter(s => s && typeof s.name === 'string' && s.name.trim() !== '' && !/^\d+%?$/.test(s.name.trim()))
                        .slice(0, 5)
                        .map((s, idx) => ({
                            id: Date.now() + Math.random() + idx,
                            name: s.name.trim(),
                            percentage: s.percentage || 80
                        }));

                    if (filtered.length > 0) {
                        this.skillsArray = filtered;
                    } else {
                        this.skillsArray = [
                            { id: Date.now(), name: 'Communication', percentage: 80 },
                            { id: Date.now()+1, name: 'Teamwork', percentage: 80 }
                        ];
                    }
                    this.updateSkillsText();
                }
            },

            // ========== استدعاء api.generate القديم (لغير المهارات) ==========
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
                if (!position) return alert(window.translations.alertEnterJobTitleFirst || 'يرجى كتابة المسمى الوظيفي أولاً');
                if (!company) return alert(window.translations.alertEnterCompanyFirst || 'يرجى كتابة اسم الشركة أولاً (لتحسين دقة التوليد)');
                
                let context = `Job Title: ${position}\nCompany: ${company}`;
                if (exp.start_date) {
                    context += `\nStart: ${exp.start_date}`;
                    if (exp.end_date) context += `\nEnd: ${exp.end_date}`;
                    else if (exp.is_current) context += `\nEnd: Present`;
                }
                
                this.experiences[index].description = window.translations.aiGenerating || '⏳ جاري التوليد...';
                const result = await this.callAiApi('description', context);
                this.experiences[index].description = result || "";
            },

            async generateSummaryAI() {
                if (!this.job_title) return alert(window.translations.alertEnterJobTitleFirst || 'أدخل المسمى الوظيفي أولاً');
                
                let context = `Target Job: ${this.job_title}`;
                if (this.experiences.length) {
                    let exp = this.experiences.filter(e => e.position && e.company)
                        .map(e => `${e.position} at ${e.company}: ${e.description?.substring(0,100) || ''}`)
                        .join('; ');
                    if (exp) context += `\nExperience: ${exp}`;
                }
                if (this.educations.length) {
                    let edu = this.educations.filter(e => e.degree || e.field_of_study)
                        .map(e => `${e.degree} in ${e.field_of_study} from ${e.institution}`)
                        .join('; ');
                    if (edu) context += `\nEducation: ${edu}`;
                }
                if (this.skillsTextForAI) context += `\nSkills: ${this.skillsTextForAI}`;
                if (this.languages.length) {
                    let lang = this.languages.filter(l => l.name).map(l => `${l.name} (${l.proficiency})`).join(', ');
                    if (lang) context += `\nLanguages: ${lang}`;
                }
                
                this.summary = window.translations.aiWritingSummary || '⏳ جاري كتابة ملخص مهني...';
                const result = await this.callAiApi('summary', context);
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
                        skills: this.skillsTextForAI,
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
                        
                        if (imp.skills) {
                            let skillsArray = [];
                            if (Array.isArray(imp.skills)) {
                                skillsArray = imp.skills;
                            } else if (typeof imp.skills === 'string') {
                                try { skillsArray = JSON.parse(imp.skills); } catch (e) {
                                    const names = imp.skills.split(',').map(s => s.trim()).filter(s => s);
                                    skillsArray = names.map(name => ({ name, percentage: 80 }));
                                }
                            }
                            if (Array.isArray(skillsArray)) {
                                this.skillsArray = skillsArray
                                    .filter(s => s && typeof s.name === 'string' && s.name.trim() !== '' && !/^\d+%?$/.test(s.name.trim()))
                                    .slice(0,5)
                                    .map((s, idx) => ({
                                        id: Date.now() + Math.random() + idx,
                                        name: s.name.trim(),
                                        percentage: s.percentage || 80
                                    }));
                                this.updateSkillsText();
                            }
                        }
                        
                        if (imp.experiences?.length) this.experiences = imp.experiences.map((exp, idx) => ({ ...exp, id: this.experiences[idx]?.id || Date.now() + Math.random() }));
                        if (imp.educations?.length) this.educations = imp.educations.map((edu, idx) => ({ ...edu, id: this.educations[idx]?.id || Date.now() + Math.random() }));
                        if (imp.languages?.length) this.languages = imp.languages.map((lang, idx) => ({ ...lang, id: this.languages[idx]?.id || Date.now() + Math.random() }));
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
                            this.skillsArray = data.data.skills.map((item, idx) => ({
                                id: Date.now() + idx,
                                name: typeof item === 'string' ? item : (item.name || `Skill ${idx+1}`),
                                percentage: item.percentage || 80
                            }));
                            this.updateSkillsText();
                        }
                        if (data.data.extra_sections?.length) this.extra_sections = data.data.extra_sections;
                        if (data.remaining_credits !== undefined) this.aiCredits = data.remaining_credits;
                        alert(window.translations.alertDataExtracted || 'تم استخراج البيانات بنجاح!');
                    }
                } catch (err) { alert(window.translations.alertFileProcessingFailed || 'فشل معالجة الملف'); }
                finally { this.isUploading = false; e.target.value = ''; }
            },

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
                        this.cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1, dragMode: 'move' });
                    }, 250);
                };
                reader.readAsDataURL(file);
            },

            saveCroppedImage() {
                if (!this.cropper) return;
                this.croppedPhotoData = this.cropper.getCroppedCanvas({ width: 400, height: 400 }).toDataURL('image/jpeg');
                this.closeCropper();
            },

            cancelCropping() { this.closeCropper(); },

            closeCropper() {
                this.showCropperModal = false;
                if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
            },

            showNameWarning() {
                if (this.isEditMode && !this.nameLocked && this.nameChangesLeft === 1) {
                    alert(window.translations.alertNameChangeWarning || '⚠️ تحذير: هذا آخر تغيير مسموح به للاسم.');
                }
            }
        };
    }
</script>