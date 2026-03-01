import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { User, LogIn, Mail, Lock, ArrowLeft, Loader2, Link as LinkIcon, Briefcase } from 'lucide-react';

export default function Register({ specialities }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        linkedin_url: '',
        speciality_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('register'));
    };

    return (
        <>
            <Head title="عضوية جديدة" />
            
            <div className="min-h-screen grid grid-cols-1 lg:grid-cols-2 bg-white" dir="rtl">
                
                {/* Right Side - Visual/Brand */}
                <div className="hidden lg:flex flex-col justify-between bg-slate-900 relative overflow-hidden p-12 text-white">
                    {/* Background Gradients */}
                    <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-600/20 blur-[120px] rounded-full pointer-events-none -translate-y-1/2 translate-x-1/2" />
                    <div className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-indigo-600/20 blur-[100px] rounded-full pointer-events-none translate-y-1/2 -translate-x-1/2" />
                    
                    {/* Content */}
                    <div className="relative z-10">
                        <Link href="/" className="inline-flex items-center gap-3 hover:opacity-80 transition-opacity">
                            <div className="bg-white/10 p-2 rounded-xl backdrop-blur-md border border-white/10">
                                <img src="/images/logo.png" alt="Logo" className="h-8 w-8 object-contain brightness-0 invert" />
                            </div>
                            <span className="font-black text-xl tracking-tight">تعريب</span>
                        </Link>
                    </div>

                    <div className="relative z-10 max-w-lg space-y-6">
                        <h2 className="text-5xl font-black leading-tight">
                            مرحباً بك في <span className="text-blue-400">مجتمع المعرفة</span>
                        </h2>
                        <p className="text-lg text-slate-400 font-medium leading-relaxed">
                            انضم إلينا كمتخصص وتفاعل مع أحدث المصطلحات التقنية. هذه المنصة محصورة للمتخصصين لضمان دقة الجودة المعرفية، لذلك حسابك سيمر عبر مراجعة بسيطة.
                        </p>
                    </div>

                    <div className="relative z-10 flex items-center gap-4 text-sm text-slate-500 font-medium">
                        <span>© 2026 تعريب</span>
                        <span className="w-1 h-1 rounded-full bg-slate-700" />
                        <span>جميع الحقوق محفوظة</span>
                    </div>
                </div>

                {/* Left Side - Form */}
                <div className="flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-y-auto w-full">
                    <Link href="/" className="absolute top-6 left-6 flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors text-sm font-bold bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-full z-10">
                        <ArrowLeft className="h-4 w-4" />
                        <span>العودة للرئيسية</span>
                    </Link>

                    <div className="w-full max-w-md space-y-8 mt-12 mb-12 lg:my-auto">
                        <div className="text-center space-y-2">
                            <h1 className="text-3xl font-black text-slate-900">حساب جديد</h1>
                            <p className="text-slate-500 font-medium">الرجاء إدخال بياناتك للمتابعة</p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div className="space-y-1.5">
                                <Label htmlFor="name" className="text-sm font-bold text-slate-700">الاسم الكامل</Label>
                                <div className="relative">
                                    <Input
                                        id="name"
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className="h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        placeholder="محمد أحمد"
                                        required
                                        autoFocus
                                    />
                                    <User className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.name && <p className="text-red-500 text-xs font-medium">{errors.name}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="email" className="text-sm font-bold text-slate-700">البريد الإلكتروني</Label>
                                <div className="relative">
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className="h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        placeholder="name@example.com"
                                        required
                                    />
                                    <Mail className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.email && <p className="text-red-500 text-xs font-medium">{errors.email}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="password" className="text-sm font-bold text-slate-700">كلمة المرور</Label>
                                <div className="relative">
                                    <Input
                                        id="password"
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className="h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        placeholder="••••••••"
                                        required
                                    />
                                    <Lock className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.password && <p className="text-red-500 text-xs font-medium">{errors.password}</p>}
                            </div>
                            
                            <div className="space-y-1.5">
                                <Label htmlFor="password_confirmation" className="text-sm font-bold text-slate-700">تأكيد كلمة المرور</Label>
                                <div className="relative">
                                    <Input
                                        id="password_confirmation"
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        className="h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        placeholder="••••••••"
                                        required
                                    />
                                    <Lock className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.password_confirmation && <p className="text-red-500 text-xs font-medium">{errors.password_confirmation}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="linkedin_url" className="text-sm font-bold text-slate-700">رابط حسابك في لينكدإن</Label>
                                <div className="relative">
                                    <Input
                                        id="linkedin_url"
                                        type="url"
                                        value={data.linkedin_url}
                                        onChange={(e) => setData('linkedin_url', e.target.value)}
                                        className="h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        placeholder="https://linkedin.com/in/..."
                                        required
                                    />
                                    <LinkIcon className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.linkedin_url && <p className="text-red-500 text-xs font-medium">{errors.linkedin_url}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <Label htmlFor="speciality_id" className="text-sm font-bold text-slate-700">التخصص</Label>
                                <div className="relative">
                                    <select
                                        id="speciality_id"
                                        value={data.speciality_id}
                                        onChange={(e) => setData('speciality_id', e.target.value)}
                                        className="w-full h-12 pr-11 pl-4 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500/20 text-start"
                                        required
                                    >
                                        <option value="" disabled>اختر تخصصك</option>
                                        {specialities.map(sp => (
                                            <option key={sp.id} value={sp.id}>{sp.name}</option>
                                        ))}
                                    </select>
                                    <Briefcase className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.speciality_id && <p className="text-red-500 text-xs font-medium">{errors.speciality_id}</p>}
                            </div>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] mt-4"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                                        جاري التسجيل...
                                    </>
                                ) : (
                                    "طلب الانضمام"
                                )}
                            </Button>
                        </form>

                        <div className="text-center">
                            <p className="text-sm font-medium text-slate-500">
                                لديك حساب بالفعل؟{' '}
                                <Link href={route('login')} className="text-blue-600 hover:text-blue-700 font-bold hover:underline transition-all">
                                    سجل الدخول
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');
                
                body {
                    font-family: 'Tajawal', sans-serif;
                }
            `}} />
        </>
    );
}

