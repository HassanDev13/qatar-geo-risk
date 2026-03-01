import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Hourglass } from 'lucide-react';

export default function Waiting() {
    return (
        <>
            <Head title="قيد المراجعة | تعريب" />
            
            <div className="min-h-screen bg-slate-50 text-slate-900 font-arabic selection:bg-blue-100 selection:text-blue-900" dir="rtl">

                <header className="relative min-h-screen flex items-center justify-center overflow-hidden">
                    <div className="absolute top-0 right-0 w-full h-full pointer-events-none">
                        <div className="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] bg-blue-100/50 blur-[120px] rounded-full" />
                        <div className="absolute bottom-0 left-0 w-[300px] h-[300px] bg-indigo-50/50 blur-[100px] rounded-full" />
                    </div>

                    <div className="container mx-auto px-4 relative z-10 text-center">
                        <div className="flex justify-center mb-8">
                            <div className="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-2xl shadow-blue-900/5 ring-1 ring-slate-100 relative">
                                <Hourglass className="w-10 h-10 text-blue-600 animate-pulse" />
                            </div>
                        </div>

                        <div className="space-y-5 mb-10">
                            <h2 className="text-3xl sm:text-5xl md:text-7xl font-black text-slate-900 tracking-tight leading-[1.2] md:leading-[1.1]">
                                حسابك الآن <br />
                                <span className="text-blue-600">قيد المراجعة</span>
                            </h2>
                            <p className="text-slate-500 text-base md:text-xl max-w-2xl mx-auto leading-relaxed font-medium px-2">
                                لقد تم استلام طلب التأكيد بنجاح. نعمل حالياً على التحقق من بيانات التخصص للتأكد من انضمامك لمجتمعنا التقني المتخصص. شكراً لتفهمك وصبرك!
                            </p>
                        </div>

                        <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2 px-4 max-w-sm mx-auto">
                            <Link 
                                href="/" 
                                className="w-full inline-flex items-center justify-center gap-2 bg-white text-slate-600 px-6 py-4 rounded-[2rem] font-bold border border-slate-200 hover:border-blue-200 hover:text-blue-600 transition-colors active:scale-95 text-base shadow-xl shadow-blue-900/5 group"
                            >
                                <span>العودة للرئيسية</span>
                                <ArrowLeft className="h-5 w-5 group-hover:-translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>
                </header>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;900&display=swap');
                .font-arabic { font-family: 'Tajawal', sans-serif; }
            `}} />
        </>
    );
}
