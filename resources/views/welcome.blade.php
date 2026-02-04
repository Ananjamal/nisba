<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نسبة | منصة التسويق بالعمولة للحلول السحابية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230ea5e9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4h-2v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="antialiased text-slate-900 bg-white selection:bg-primary-500 selection:text-white">
    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 glass-nav border-none">
        <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-900 text-white rounded-xl flex items-center justify-center font-black text-xl shadow-lg shadow-primary-900/10">%</div>
                <span class="text-2xl font-black tracking-tight text-slate-900">نسبة</span>
            </div>

            <div class="hidden md:flex items-center gap-10">
                <a href="#features" class="text-sm font-bold text-slate-500 hover:text-primary-600 transition-colors uppercase tracking-widest">المميزات</a>
                <a href="#how-it-works" class="text-sm font-bold text-slate-500 hover:text-primary-600 transition-colors uppercase tracking-widest">كيف يعمل؟</a>
                <a href="#faq" class="text-sm font-bold text-slate-500 hover:text-primary-600 transition-colors uppercase tracking-widest">الأسئلة</a>
            </div>

            <div class="flex items-center gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="btn-primary py-3 px-6 text-sm">لوحة التحكم</a>
                @else
                <a href="{{ route('login') }}" class="text-sm font-black text-slate-900 hover:text-primary-600 transition-colors">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" class="btn-primary py-3 px-8 text-sm shadow-xl shadow-primary-900/10">{{ __('ابدأ الآن') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-48 pb-32 overflow-hidden hero-pattern">
        <!-- Background Orbs -->
        <div class="absolute top-0 right-0 -mr-24 w-[600px] h-[600px] bg-primary-50 rounded-full blur-[100px] -z-10"></div>
        <div class="absolute bottom-0 left-0 -ml-24 w-[500px] h-[500px] bg-indigo-50 rounded-full blur-[100px] -z-10"></div>

        <div class="max-w-7xl mx-auto px-6 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 text-primary-700 rounded-full font-bold text-xs uppercase tracking-widest mb-10 animate-bounce">
                🚀 بوابتك للنمو المالي الرقمي
            </div>
            <h1 class="text-6xl md:text-8xl font-black text-slate-900 mb-8 leading-[1.1] tracking-tight">
                اربح عمولات مجزية <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-indigo-600">من شركاء النجاح</span>
            </h1>
            <p class="text-xl text-slate-500 max-w-2xl mx-auto mb-16 font-medium leading-relaxed">
                منصة نسبة هي بوابتك للارتقاء بدخلك من خلال ترشيح أفضل الحلول السحابية المحاسبية لعملائك ومتابعة أرباحك بكل شفافية واحترافية.
            </p>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                <a href="{{ route('register') }}" class="btn-primary text-xl px-12 py-6 min-w-[240px]">
                    سجل كشريك نجاح
                </a>
                <a href="#how-it-works" class="btn-secondary text-xl px-12 py-6 min-w-[240px]">
                    كيف تعمل المنصة؟
                </a>
            </div>

            <!-- Dashboard Preview Placeholder -->
            <div class="mt-24 relative max-w-5xl mx-auto">
                <div class="p-4 bg-white/50 border border-white/50 rounded-[3rem] shadow-2xl backdrop-blur-sm">
                    <div class="bg-slate-900 h-[500px] rounded-[2.5rem] overflow-hidden flex items-center justify-center relative">
                        <div class="text-white/20 text-9xl font-black italic">NISBA</div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
                    </div>
                </div>
                <!-- Floating Elements -->
                <div class="absolute -top-10 -right-10 glass-card p-6 shadow-2xl rotate-3 animate-pulse">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white">💰</div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-400">عمولة جديدة</div>
                            <div class="text-lg font-black text-slate-900">+500 ريال</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners -->
    <section class="py-24 border-y border-slate-100 bg-slate-50/30">
        <div class="max-w-7xl mx-auto px-6">
            <p class="text-center text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-16">نحن مدعومون بأفضل الحلول السحابية</p>
            <div class="flex flex-wrap justify-center items-center gap-16 md:gap-24 grayscale opacity-30">
                <img src="https://nisba.me/assets/img/qoyod.png" class="h-10 md:h-12 object-contain" alt="Qoyod">
                <img src="https://nisba.me/assets/img/daftra.png" class="h-10 md:h-12 object-contain" alt="Daftra">
                <img src="https://nisba.me/assets/img/zoho.png" class="h-10 md:h-12 object-contain" alt="Zoho">
            </div>
        </div>
    </section>

    <!-- Steps -->
    <section id="how-it-works" class="py-32 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-24">
                <h2 class="section-title">ثلاث خطوات بسيطة لتبدأ</h2>
                <p class="section-subtitle mx-auto">صممنا العملية لتكون سهلة وشفافة حتى تتمكن من التركيز على ما تفعله بشكل أفضل.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="premium-card p-10 group">
                    <div class="w-20 h-20 bg-primary-50 rounded-3xl flex items-center justify-center text-4xl mb-8 group-hover:scale-110 transition-transform duration-500">📝</div>
                    <h3 class="text-2xl font-black mb-4">1. سجل حسابك</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">انضم لشبكة شركاء نسبة خلال دقائق وابدأ رحلة الأرباح مع نظام مرن واحترافي.</p>
                </div>
                <div class="premium-card p-10 group">
                    <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center text-4xl mb-8 group-hover:scale-110 transition-transform duration-500">🤝</div>
                    <h3 class="text-2xl font-black mb-4">2. أضف عملاءك</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">أضف بيانات العملاء المحتملين من خلال لوحة التحكم وتابع تقدمهم لحظة بلحظة.</p>
                </div>
                <div class="premium-card p-10 group">
                    <div class="w-20 h-20 bg-luxury-gold-light rounded-3xl flex items-center justify-center text-4xl mb-8 group-hover:scale-110 transition-transform duration-500">💰</div>
                    <h3 class="text-2xl font-black mb-4">3. احصل على عمولتك</h3>
                    <p class="text-slate-500 font-medium leading-relaxed">بمجرد إتمام الصفقة من قبل مزود الخدمة، تضاف العمولة لرصيدك بشكل آلي وفوري.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-32 bg-slate-950 text-white overflow-hidden relative">
        <!-- Background Glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-500/10 rounded-full blur-[150px] -z-0"></div>

        <div class="max-w-4xl mx-auto px-6 relative z-10">
            <h2 class="text-4xl md:text-5xl font-black text-center mb-20">الأسئلة الشائعة</h2>
            <div class="space-y-6">
                <div class="bg-white/5 border border-white/10 p-8 rounded-[2.5rem] backdrop-blur-md">
                    <h4 class="text-xl font-black mb-4 text-primary-400">ما هي فكرة منصة نسبة؟</h4>
                    <p class="text-slate-400 font-medium leading-relaxed">نسبة هي منصة تجمع بين المسوقين ومزودي الخدمات السحابية لتسهيل عملية الإحالة وتتبع العمولات بدقة وشفافية عالية.</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-8 rounded-[2.5rem] backdrop-blur-md">
                    <h4 class="text-xl font-black mb-4 text-primary-400">متى يمكنني سحب أرباحي؟</h4>
                    <p class="text-slate-400 font-medium leading-relaxed">بمجرد تحول حالة العميل إلى "تم البيع" وتأكيد الدفع من مزود الخدمة، يمكنك طلب سحب رصيدك فوراً عبر الوسائل المتاحة.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="max-w-7xl mx-auto px-6 py-32">
        <div class="relative bg-primary-900 rounded-[4rem] p-16 md:p-24 overflow-hidden text-center">
            <div class="absolute inset-0 opacity-20 hero-pattern"></div>
            <div class="relative z-10">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-10 leading-tight">جاهز لزيادة أرباحك <br> بطريقة احترافية؟</h2>
                <a href="{{ route('register') }}" class="btn-gold py-6 px-16 text-2xl mx-auto inline-flex">ابدأ رحلتك الآن</a>
            </div>
            <!-- Glow effect -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl"></div>
        </div>
    </section>

    <footer class="py-12 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-8">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center font-black text-lg">%</div>
                <span class="text-xl font-black tracking-tight text-slate-900">نسبة</span>
            </div>
            <p class="text-slate-400 font-bold text-sm">جميع الحقوق محفوظة &copy; {{ date('Y') }} منصة نسبة</p>
            <div class="flex gap-6">
                <a href="#" class="text-slate-400 hover:text-primary-600 transition-colors">Twitter</a>
                <a href="#" class="text-slate-400 hover:text-primary-600 transition-colors">LinkedIn</a>
            </div>
        </div>
    </footer>
</body>

</html>