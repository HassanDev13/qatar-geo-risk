import { useState } from "react";
import { Loader2, Send, CheckCircle2 } from "lucide-react";
import { Button } from "@/Components/ui/button";

export default function ContactForm() {
    const [loading, setLoading] = useState(false);
    const [success, setSuccess] = useState(false);
    const [errors, setErrors] = useState({});

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});

        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('/contact', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (response.ok) {
                setSuccess(true);
                e.target.reset();
            } else {
                const data = await response.json();
                if (data.errors) {
                    setErrors(data.errors);
                }
            }
        } catch (error) {
            console.error('Error submitting form:', error);
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <div className="flex flex-col items-center justify-center h-full text-center space-y-4 py-10">
                <div className="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-2">
                    <CheckCircle2 className="h-8 w-8 text-green-600" />
                </div>
                <h4 className="text-xl font-black text-slate-800">تم استلام رسالتك!</h4>
                <p className="text-slate-500 font-medium">سنتواصل معك في أقرب وقت ممكن.</p>
                <Button variant="outline" onClick={() => setSuccess(false)} className="mt-4">
                    إرسال رسالة أخرى
                </Button>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-4 text-right" dir="rtl">
            <h4 className="font-bold text-slate-800 text-lg mb-2">أرسل لنا رسالة</h4>
            
            <div className="space-y-1">
                <label htmlFor="name" className="text-xs font-bold text-slate-500 px-1">الاسم الكامل</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    required
                    className="w-full h-11 px-4 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all text-sm font-medium"
                    placeholder="اسمك الكريم"
                />
                {errors.name && <p className="text-red-500 text-xs px-1">{errors.name[0]}</p>}
            </div>

            <div className="space-y-1">
                <label htmlFor="email" className="text-xs font-bold text-slate-500 px-1">البريد الإلكتروني</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    className="w-full h-11 px-4 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all text-sm font-medium"
                    placeholder="example@domain.com"
                />
                {errors.email && <p className="text-red-500 text-xs px-1">{errors.email[0]}</p>}
            </div>

            <div className="space-y-1">
                <label htmlFor="subject" className="text-xs font-bold text-slate-500 px-1">الموضوع (اختياري)</label>
                <input
                    type="text"
                    name="subject"
                    id="subject"
                    className="w-full h-11 px-4 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all text-sm font-medium"
                    placeholder="اقتراح، استفسار، تعاون..."
                />
                {errors.subject && <p className="text-red-500 text-xs px-1">{errors.subject[0]}</p>}
            </div>

            <div className="space-y-1">
                <label htmlFor="message" className="text-xs font-bold text-slate-500 px-1">الرسالة</label>
                <textarea
                    name="message"
                    id="message"
                    required
                    rows={4}
                    className="w-full p-4 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all text-sm font-medium resize-none"
                    placeholder="اكتب رسالتك هنا..."
                />
                {errors.message && <p className="text-red-500 text-xs px-1">{errors.message[0]}</p>}
            </div>

            <Button
                type="submit"
                disabled={loading}
                className="w-full h-12 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-900/10 font-black text-sm gap-2"
            >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <Send className="h-4 w-4 ml-1" />}
                {loading ? 'جاري الإرسال...' : 'إرسال الرسالة'}
            </Button>
        </form>
    );
}
