document.addEventListener('alpine:init', () => {
    Alpine.data('resumeForm', () => ({
        step: 1,
        maxStep: 6,
        stepLabels: [
            'المعلومات الشخصية', 
            'المؤهلات الأكاديمية', 
            'الخبرات المهنية', 
            'المهارات والملخص', 
            'اللغات', 
            'اللمسات الأخيرة'
        ],

        // حقول البيانات
        full_name: '',
        job_title: '',
        email: '',
        phone: '',
        address: '',
        skills: '',
        summary: '',
        
        // المصفوفات
        educations: [{ id: Date.now(), institution: '', degree: '', graduation_year: '' }],
        experiences: [{ id: Date.now(), company: '', position: '', start_date: '', end_date: '', description: '' }],
        languages: [{ id: Date.now(), name: '', proficiency: 'متوسط' }],

        // حالة الواجهة
        isUploading: false,
        isReviewing: false,
        reviewSuccessMessage: '',
        showCropperModal: false,
        cropper: null,
        croppedPhotoData: null,
        shape: 'square',
        fieldErrors: window.laravelErrors || {},

        // تهيئة الصورة والقص
        initFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const imgElement = document.getElementById('cropper-image');
                imgElement.src = event.target.result;
                this.showCropperModal = true;

                if (this.cropper) this.cropper.destroy();

                setTimeout(() => {
                    this.cropper = new Cropper(imgElement, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move'
                    });
                }, 200);
            };
            reader.readAsDataURL(file);
        },

        saveCroppedImage() {
            const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400 });
            this.croppedPhotoData = canvas.toDataURL('image/jpeg');
            this.showCropperModal = false;
        },

        // --- دوال الذكاء الاصطناعي المرتبطة بـ AiGenerationController --- //

        async callAiApi(type, context) {
            try {
                const res = await fetch(window.routes.aiGenerate, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value 
                    },
                    body: JSON.stringify({ type: type, context: context })
                });
                
                const data = await res.json();
                
                if (!res.ok) {
                    alert(data.error || 'حدث خطأ في الاتصال بالذكاء الاصطناعي');
                    return null;
                }
                
                return data.result;
            } catch (e) { 
                alert('حدث خطأ غير متوقع.');
                return null;
            }
        },

        async generateExperienceAI(index) {
            const pos = this.experiences[index].position;
            if (!pos) return alert('يرجى كتابة المسمى الوظيفي أولاً لنتمكن من توليد المهام.');
            
            this.experiences[index].description = "⏳ جاري توليد المهام بالذكاء الاصطناعي...";
            const result = await this.callAiApi('description', pos);
            if (result) {
                this.experiences[index].description = result;
            } else {
                this.experiences[index].description = "";
            }
        },

        async generateSkillsAI() {
            if (!this.job_title) return alert('يرجى كتابة المسمى الوظيفي في الخطوة الأولى.');
            this.skills = "⏳ جاري اقتراح المهارات...";
            const result = await this.callAiApi('skills', this.job_title);
            if (result) this.skills = result;
        },

        async generateSummaryAI() {
            if (!this.job_title) return alert('يرجى كتابة المسمى الوظيفي في الخطوة الأولى.');
            this.summary = "⏳ جاري كتابة الملخص...";
            const result = await this.callAiApi('summary', this.job_title);
            if (result) this.summary = result;
        },

        // --- دالة استخراج البيانات المرتبطة بـ AiResumeController --- //

        async uploadAndParseCV(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            this.isUploading = true;
            
            const formData = new FormData();
            // تم التعديل لتطابق الكونترولر (cv_file بدلاً من cv)
            formData.append('cv_file', file); 

            try {
                const response = await fetch(window.routes.cvParse, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    alert(data.error || 'حدث خطأ أثناء قراءة الملف');
                    return;
                }

                if (data.success && data.data) {
                    const aiData = data.data;

                    // 1. تعبئة البيانات الشخصية
                    if (aiData.personal_details) {
                        this.full_name = aiData.personal_details.full_name || '';
                        this.job_title = aiData.personal_details.job_title || '';
                        this.email = aiData.personal_details.email || '';
                        this.phone = aiData.personal_details.phone || '';
                        this.address = aiData.personal_details.address || '';
                        this.summary = aiData.personal_details.summary || '';
                    }

                    // 2. تعبئة الخبرات
                    if (aiData.experiences && aiData.experiences.length > 0) {
                        this.experiences = aiData.experiences.map(exp => ({
                            id: Date.now() + Math.random(),
                            company: exp.company || '',
                            position: exp.position || '',
                            start_date: exp.start_date || '',
                            end_date: exp.end_date || '',
                            description: exp.description || ''
                        }));
                    }

                    // 3. تعبئة المؤهلات
                    if (aiData.educations && aiData.educations.length > 0) {
                        this.educations = aiData.educations.map(edu => ({
                            id: Date.now() + Math.random(),
                            institution: edu.institution || '',
                            degree: edu.degree || '',
                            graduation_year: edu.graduation_year || ''
                        }));
                    }

                    // 4. تعبئة المهارات (تحويلها من مصفوفة إلى نص مفصول بفواصل)
                    if (aiData.skills && aiData.skills.length > 0) {
                        this.skills = aiData.skills.map(s => s.name).join(', ');
                    }

                    // 5. تعبئة اللغات
                    if (aiData.languages && aiData.languages.length > 0) {
                        this.languages = aiData.languages.map(lang => ({
                            id: Date.now() + Math.random(),
                            name: lang.name || '',
                            proficiency: lang.proficiency || 'متوسط'
                        }));
                    }
                    
                    alert('تم سحب البيانات بنجاح!');
                }
            } catch (err) { 
                console.error(err);
                alert('حدث خطأ في الشبكة.');
            } finally { 
                this.isUploading = false; 
                // تصفير حقل الإدخال ليتمكن المستخدم من رفع نفس الملف مرة أخرى إذا أراد
                e.target.value = ''; 
            }
        },
        async reviewEntireResumeAI() {
            this.isReviewing = true;
            this.reviewSuccessMessage = '';
            
            // تجميع بيانات السيرة الحالية
            const payload = {
                full_name: this.full_name,
                job_title: this.job_title,
                email: this.email,
                phone: this.phone,
                address: this.address,
                summary: this.summary,
                skills: this.skills, // سيتم إرساله كنص مفصول بفواصل
                educations: this.educations,
                experiences: this.experiences,
                languages: this.languages
            };

            try {
                const res = await fetch(window.routes.aiReview, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value 
                    },
                    body: JSON.stringify(payload)
                });
                
                const responseData = await res.json();
                
                if (!res.ok) {
                    alert(responseData.error || 'حدث خطأ أثناء تنقيح السيرة');
                    return;
                }

                if (responseData.success && responseData.data) {
                    const improvedData = responseData.data;

                    // تحديث البيانات الشخصية (الكونترولر يضعها إما بشكل مباشر أو داخل personal_details)
                    const details = improvedData.personal_details || improvedData;
                    
                    this.full_name = details.full_name || this.full_name;
                    this.job_title = details.job_title || this.job_title;
                    this.email = details.email || this.email;
                    this.phone = details.phone || this.phone;
                    this.address = details.address || this.address;
                    this.summary = details.summary || this.summary;

                    // تحديث المصفوفات
                    if (improvedData.experiences) this.experiences = improvedData.experiences;
                    if (improvedData.educations) this.educations = improvedData.educations;
                    if (improvedData.languages) this.languages = improvedData.languages;
                    
                    // معالجة المهارات: الكونترولر يتوقعها كمصفوفة [{name:"..."}]، فنقوم بإعادتها لنص مفصول بفواصل للواجهة
                    if (improvedData.skills) {
                        if (Array.isArray(improvedData.skills)) {
                            this.skills = improvedData.skills.map(s => s.name || s).join('، ');
                        } else {
                            this.skills = improvedData.skills;
                        }
                    }

                    this.reviewSuccessMessage = 'تم تنقيح وتصحيح السيرة بنجاح! راجع البيانات في الخطوات السابقة أو قم بالحفظ النهائي.';
                    
                    // إخفاء الرسالة بعد 10 ثوانٍ
                    setTimeout(() => { this.reviewSuccessMessage = ''; }, 10000);
                }
            } catch (err) {
                console.error(err);
                alert('حدث خطأ في الاتصال بالشبكة أثناء المراجعة.');
            } finally {
                this.isReviewing = false;
            }
        }

    }));
});