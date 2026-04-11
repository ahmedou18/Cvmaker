<style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap');
        [x-cloak] { display: none !important; }
        body, button, input, textarea, select { font-family: 'Cairo', sans-serif !important; }
        .page-content { background-color: #f7f9fa; min-height: 100vh; }
        
        /* تصميم الهوية الحادة */
        .sharp-card, .sharp-input, .sharp-btn-primary, .sharp-btn-secondary, .sharp-btn-ai, .step-link, .modal-box { border-radius: 0px !important; }
        .sharp-card { background-color: #ffffff; border: 1px solid #e1e8ed; padding: 2.5rem; margin-bottom: 2rem; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .sharp-btn-primary { background-color: #2e7bb6; color: #ffffff; padding: 0.85rem 2.5rem; font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; transition: 0.2s; }
        .sharp-btn-primary:hover { background-color: #235e8d; }
        .sharp-btn-primary:disabled { background-color: #94a3b8; cursor: not-allowed; }
        .sharp-btn-ai:disabled { opacity:0.5; cursor:not-allowed; }
        .sharp-btn-secondary { background-color: #ffffff; color: #2e7bb6; padding: 1rem; font-weight: 700; border: 1px solid #2e7bb6; width: 100%; text-align: center; cursor: pointer; transition: 0.2s; }
        .sharp-btn-ai { background-color: #f0f7fb; color: #2e7bb6; padding: 0.4rem 0.8rem; font-weight: 700; border: 1px solid #2e7bb6; cursor: pointer; font-size: 0.8rem; }
        .sharp-input { border: 1px solid #d1d5db; padding: 1rem; width: 100%; background-color: #ffffff; transition: 0.2s; }
        .sharp-input:focus { border-color: #2e7bb6; outline: 2px solid #2e7bb6; }
        .sharp-label { color: #374151; font-weight: 700; margin-bottom: 0.5rem; display: block; font-size: 0.9rem; }
        
        /* القائمة الجانبية */
        .step-link { display: flex; align-items: center; padding: 1.5rem; border: 1px solid #e1e8ed; margin-bottom: -1px; background: #fff; color: #6b7280; cursor: pointer; }
        .step-link.active { background-color: #f0f7fb; color: #2e7bb6; border-right: 5px solid #2e7bb6; font-weight: 800; }
        .step-number { margin-left: 1rem; font-size: 1.2rem; opacity: 0.5; }
        
        /* قسم الـ AI المقفل */
        .ai-locked-section { 
            position: relative;
            opacity: 0.7; 
            filter: grayscale(0.5); 
            pointer-events: none; 
            background-color: #f3f4f6 !important;
            border: 2px dashed #d1d5db !important;
        }

        /* إصلاح المودال (Cropper Modal) */
        .fixed-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }
        .modal-box {
            background: white;
            width: 100%;
            max-width: 600px;
            padding: 25px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .cropper-wrapper {
            max-height: 400px;
            width: 100%;
            background: #f1f5f9;
            margin-bottom: 20px;
            overflow: hidden;
        }
        img#cropper-image { max-width: 100%; display: block; }
    </style>