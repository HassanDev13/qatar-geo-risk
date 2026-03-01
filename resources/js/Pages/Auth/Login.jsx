import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { LogIn, Mail, Lock, ArrowLeft, Loader2 } from 'lucide-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <>
            <Head title="تسجيل الدخول" />
            
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
                            مرحباً بك مجدداً في <span className="text-blue-400">مجتمع المعرفة</span>
                        </h2>
                        <p className="text-lg text-slate-400 font-medium leading-relaxed">
                            ساهم معنا في إثراء المحتوى العربي وتوحيد المصطلحات التقنية. رحلتك نحو بناء معجم عربي حديث تبدأ هنا.
                        </p>
                    </div>

                    <div className="relative z-10 flex items-center gap-4 text-sm text-slate-500 font-medium">
                        <span>© 2026 تعريب</span>
                        <span className="w-1 h-1 rounded-full bg-slate-700" />
                        <span>جميع الحقوق محفوظة</span>
                    </div>
                </div>

                {/* Left Side - Form */}
                <div className="flex items-center justify-center p-6 sm:p-12 relative">
                    <Link href="/" className="absolute top-6 left-6 flex items-center gap-2 text-slate-500 hover:text-slate-900 transition-colors text-sm font-bold bg-slate-50 hover:bg-slate-100 px-4 py-2 rounded-full">
                        <ArrowLeft className="h-4 w-4" />
                        <span>العودة للرئيسية</span>
                    </Link>

                    <div className="w-full max-w-md space-y-8">
                        <div className="text-center space-y-2">
                            <div className="inline-flex lg:hidden items-center justify-center w-12 h-12 bg-blue-50 rounded-2xl mb-4 text-blue-600">
                                <LogIn className="w-6 h-6" />
                            </div>
                            <h1 className="text-3xl font-black text-slate-900">تسجيل الدخول</h1>
                            <p className="text-slate-500 font-medium">أدخل بياناتك للمتابعة</p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
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
                                        autoFocus
                                    />
                                    <Mail className="absolute right-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 pointer-events-none" />
                                </div>
                                {errors.email && <p className="text-red-500 text-xs font-medium">{errors.email}</p>}
                            </div>

                            <div className="space-y-1.5">
                                <div className="flex items-center justify-between">
                                    <Label htmlFor="password" className="text-sm font-bold text-slate-700">كلمة المرور</Label>
                                </div>
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

                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="remember"
                                    checked={data.remember}
                                    onCheckedChange={(checked) => setData('remember', checked)}
                                    className="data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600"
                                />
                                <Label htmlFor="remember" className="text-sm font-medium text-slate-600 cursor-pointer select-none">
                                    تذكرني على هذا الجهاز
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base rounded-xl shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="mr-2 h-5 w-5 animate-spin" />
                                        جاري الدخول...
                                    </>
                                ) : (
                                    "تسجيل الدخول"
                                )}
                            </Button>
                        </form>

                        <div className="text-center">
                            <p className="text-sm font-medium text-slate-500">
                                ليس لديك حساب؟{' '}
                                <Link href={route('register')} className="text-blue-600 hover:text-blue-700 font-bold hover:underline transition-all">
                                    سجل الآن
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
