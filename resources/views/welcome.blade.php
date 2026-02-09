<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'حليف') }} - منصة التسويق بالعمولة</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-cairo bg-white text-gray-900 selection:bg-yellow-400 selection:text-black">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 bg-primary-900 border-b border-white/10 shadow-lg py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <!-- Logo -->
            <x-application-logo class="text-white" />

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button class="focus:outline-none text-white">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="font-bold transition-colors text-sm text-gray-300 hover:text-white">المميزات</a>
                <a href="#how-it-works" class="font-bold transition-colors text-sm text-gray-300 hover:text-white">شركاء النجاح</a>
                <a href="#faq" class="font-bold transition-colors text-sm text-gray-300 hover:text-white">الأسئلة الشائعة</a>
            </div>

            <!-- Auth Buttons -->
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                @auth
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="bg-primary-700 text-white px-6 py-3 rounded-xl font-bold hover:bg-primary-600 transition shadow-sm">{{ __('لوحة الإدارة') }}</a>
                @endif
                @else
                <a href="{{ route('login') }}" class="bg-yellow-400 text-black px-6 py-3 rounded-xl font-bold hover:bg-yellow-500 transition shadow-sm">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" class="bg-primary-700 text-white px-6 py-3 rounded-xl font-bold hover:bg-primary-600 transition shadow-sm">{{ __('انضم كشريك') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section (Centered & Premium) -->
    <header class="relative min-h-[850px] flex items-center justify-center overflow-hidden bg-primary-900 text-white">

        <!-- Background Effects -->
        <div class="absolute inset-0 pointer-events-none">
            <!-- Subtle Grid -->
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay"></div>
            <!-- Glows -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-7xl mx-auto">
                <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-yellow-500/10 rounded-full blur-[100px] mix-blend-screen animation-delay-2000"></div>
            </div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 text-center mt-20">

            <!-- Quality Badge -->
            <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/5 border border-white/10 text-yellow-400 text-sm font-bold mb-10 backdrop-blur-md shadow-2xl animate-fade-in-up">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-yellow-500"></span>
                </span>
                <span class="tracking-wide uppercase text-xs sm:text-sm">منصة التسويق بالعمولة الأولى</span>
            </div>

            <!-- Headline -->
            <h1 class="text-5xl sm:text-7xl md:text-8xl font-black leading-tight mb-8 tracking-tight drop-shadow-2xl animate-fade-in-up delay-100">
                أرباحك تبدأ <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-600">بشراكة ذكية</span>
            </h1>

            <!-- Subheadline -->
            <p class="text-lg sm:text-xl md:text-2xl text-blue-100/80 mb-12 max-w-3xl mx-auto leading-relaxed font-normal animate-fade-in-up delay-200">
                انضم لنخبة المسوقين في "{{ config('app.name', 'حليف') }}". نربطك بأكبر العلامات التجارية لتبني دخلاً مستداماً بأدوات احترافية وعمولات مجزية.
            </p>

            <!-- CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-5 animate-fade-in-up delay-300">
                <a href="{{ route('register') }}" style="background-color: #FFD700 !important; color: #051d2e !important;" class="w-full sm:w-auto px-10 py-5 text-lg font-black rounded-xl shadow-[0_0_30px_-5px_rgba(250,204,21,0.4)] transition-all transform hover:-translate-y-1 hover:scale-105">
                    ابدأ رحلتك الآن
                </a>
                <a href="#how-it-works" class="w-full sm:w-auto px-10 py-5 bg-white/5 hover:bg-white/10 text-white text-lg font-bold rounded-xl backdrop-blur border border-white/10 transition-all flex items-center justify-center gap-2 group">
                    <span class="group-hover:translate-x-1 transition-transform">كيف تعمل؟</span>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    </svg>
                </a>
            </div>

            <!-- Stats / Social Proof -->
            <div class="mt-24 pt-10 border-t border-white/5 grid grid-cols-2 md:grid-cols-4 gap-8 animate-fade-in-up delay-400">
                <div>
                    <p class="text-4xl font-black text-white mb-1">+500</p>
                    <p class="text-sm text-gray-400 font-medium">مسوق نشط</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-white mb-1">1.2M</p>
                    <p class="text-sm text-gray-400 font-medium">ريال عمولات</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-white mb-1">+50</p>
                    <p class="text-sm text-gray-400 font-medium">علامة تجارية</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-white mb-1">24/7</p>
                    <p class="text-sm text-gray-400 font-medium">دعم متواصل</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Partners Ticker -->
    <div class="bg-gray-50 py-10 border-b border-gray-100 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 text-center mb-8">
            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">شركتم مع كبرى الشركات التقنية</p>
        </div>
        <div class="flex items-center justify-center gap-12 md:gap-24 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
            <span class="text-2xl font-black text-gray-400 flex items-center gap-2">
                <span class="w-8 h-8 bg-gray-300 rounded-md"></span> Salla
            </span>
            <span class="text-2xl font-black text-gray-400 flex items-center gap-2">
                <span class="w-8 h-8 bg-gray-300 rounded-md"></span> Zid
            </span>
            <span class="text-2xl font-black text-gray-400 flex items-center gap-2">
                <span class="w-8 h-8 bg-gray-300 rounded-md"></span> Daftra
            </span>
            <span class="text-2xl font-black text-gray-400 flex items-center gap-2">
                <span class="w-8 h-8 bg-gray-300 rounded-md"></span> Qoyod
            </span>
        </div>
    </div>

    <!-- Features -->
    <section id="features" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <span class="text-primary-600 font-bold tracking-wider uppercase text-sm mb-2 block">مميزاتنا</span>
                <h2 class="text-3xl md:text-5xl font-black text-primary-900 mb-6">لماذا تختار منصة {{ config('app.name', 'حليف') }}؟</h2>
                <p class="text-gray-500 text-lg">نقدم لك أدوات احترافية، تتبع دقيق، وعمولات مجزية تجعل من تسويقك رحلة ممتعة ومربحة.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group p-8 rounded-[2rem] bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-2xl hover:shadow-primary-900/5 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        🚀
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">عمولات عالية وفورية</h3>
                    <p class="text-gray-500 leading-relaxed">احصل على أعلى نسب عمولة في السوق، مع نظام دفع سريع ومرن يناسب احتياجاتك.</p>
                </div>

                <!-- Feature 2 -->
                <div class="group p-8 rounded-[2rem] bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-2xl hover:shadow-primary-900/5 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">لوحة تحكم ذكية</h3>
                    <p class="text-gray-500 leading-relaxed">تابع أداء حملاتك، عدد النقرات، والتحويلات لحظة بلحظة من خلال لوحة تحكم متطورة.</p>
                </div>

                <!-- Feature 3 -->
                <div class="group p-8 rounded-[2rem] bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 hover:shadow-2xl hover:shadow-primary-900/5 transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center text-3xl mb-6 group-hover:scale-110 transition-transform">
                        🤝
                    </div>
                    <h3 class="text-xl font-black text-gray-900 mb-3">دعم متواصل</h3>
                    <p class="text-gray-500 leading-relaxed">فريق دعم مخصص لمساعدتك في كل خطوة، وتوفير المواد التسويقية اللازمة لنجاحك.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-24 bg-gray-50 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-50/50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <span class="text-yellow-500 font-bold tracking-wider uppercase text-sm mb-2 block">خطوات بسيطة</span>
                <h2 class="text-3xl md:text-5xl font-black text-primary-900 mb-6">كيف تبدأ الربح؟</h2>
                <p class="text-gray-500 text-lg">عملية سهلة وسريعة لبدء جني الأرباح معنا، في 3 خطوات فقط.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                <!-- Connecting Line (Desktop) -->
                <div class="hidden md:block absolute top-12 left-0 w-full h-0.5 bg-gray-200 -z-10"></div>

                <!-- Step 1 -->
                <div class="relative text-center group">
                    <div class="w-24 h-24 mx-auto bg-white border-4 border-white shadow-xl rounded-full flex items-center justify-center relative z-10 mb-8 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-4xl">📝</span>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-white text-sm">1</div>
                    </div>
                    <h3 class="text-2xl font-black text-primary-900 mb-4">سجل مجاناً</h3>
                    <p class="text-gray-500 leading-relaxed">أنشئ حسابك في ثوانٍ. لا توجد رسوم مخفية، التسجيل مجاني تماماً.</p>
                </div>

                <!-- Step 2 -->
                <div class="relative text-center group">
                    <div class="w-24 h-24 mx-auto bg-white border-4 border-white shadow-xl rounded-full flex items-center justify-center relative z-10 mb-8 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-4xl">🔗</span>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-white text-sm">2</div>
                    </div>
                    <h3 class="text-2xl font-black text-primary-900 mb-4">انسخ رابطك</h3>
                    <p class="text-gray-500 leading-relaxed">اختر المنتجات أو الخدمات التي تناسب جمهورك وانسخ رابط الإحالة الخاص بك.</p>
                </div>

                <!-- Step 3 -->
                <div class="relative text-center group">
                    <div class="w-24 h-24 mx-auto bg-white border-4 border-white shadow-xl rounded-full flex items-center justify-center relative z-10 mb-8 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-4xl">💰</span>
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center font-bold text-white text-sm">3</div>
                    </div>
                    <h3 class="text-2xl font-black text-primary-900 mb-4">اربح العمولات</h3>
                    <p class="text-gray-500 leading-relaxed">احصل على عمولتك فور إتمام أي عملية بيع ناجحة عبر رابطك. اسحب أرباحك بسهولة.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-primary-900 mb-4">الأسئلة الشائعة</h2>
                <p class="text-gray-500">إجابات على الأسئلة الأكثر تكراراً</p>
            </div>

            <div class="space-y-4" x-data="{ active: null }">
                <!-- FAQ Item 1 -->
                <div class="border border-gray-100 rounded-2xl bg-gray-50 overflow-hidden hover:border-primary-100 transition-colors">
                    <button @click="active === 1 ? active = null : active = 1" class="w-full flex items-center justify-between p-6 text-right">
                        <span class="font-bold text-lg text-primary-900">هل التسجيل مجاني؟</span>
                        <span class="text-primary-600 font-bold text-xl" x-text="active === 1 ? '-' : '+'">+</span>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-6 pb-6 text-gray-600 leading-relaxed bg-gray-50/50">
                        نعم، الانضمام إلى برنامج "{{ config('app.name', 'حليف') }}" مجاني تماماً ولا توجد أي رسوم شهرية أو سنوية.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="border border-gray-100 rounded-2xl bg-gray-50 overflow-hidden hover:border-primary-100 transition-colors">
                    <button @click="active === 2 ? active = null : active = 2" class="w-full flex items-center justify-between p-6 text-right">
                        <span class="font-bold text-lg text-primary-900">متى يتم تحويل الأرباح؟</span>
                        <span class="text-primary-600 font-bold text-xl" x-text="active === 2 ? '-' : '+'">+</span>
                    </button>
                    <div x-show="active === 2" x-collapse class="px-6 pb-6 text-gray-600 leading-relaxed bg-gray-50/50">
                        يتم تحويل الأرباح شهرياً بمجرد وصول رصيدك إلى الحد الأدنى للسحب وهو 200 ريال.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="border border-gray-100 rounded-2xl bg-gray-50 overflow-hidden hover:border-primary-100 transition-colors">
                    <button @click="active === 3 ? active = null : active = 3" class="w-full flex items-center justify-between p-6 text-right">
                        <span class="font-bold text-lg text-primary-900">كيف يمكنني تتبع مبيعاتي؟</span>
                        <span class="text-primary-600 font-bold text-xl" x-text="active === 3 ? '-' : '+'">+</span>
                    </button>
                    <div x-show="active === 3" x-collapse class="px-6 pb-6 text-gray-600 leading-relaxed bg-gray-50/50">
                        نوفر لك لوحة تحكم شاملة تعرض لك عدد الزيارات، النقرات، والمبيعات المحققة بشكل فوري ودقيق.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4">
        <div class="max-w-7xl mx-auto bg-primary-900 rounded-[3rem] relative overflow-hidden text-center py-24 px-6 md:px-12 shadow-2xl">
            <!-- Background Decorations -->
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <div class="absolute top-10 left-10 w-32 h-32 bg-yellow-500/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-10 right-10 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl animate-pulse delay-700"></div>

            <div class="relative z-10">
                <h2 class="text-4xl md:text-6xl font-black text-white mb-8">جاهز لتبدأ رحلة الربح؟</h2>
                <p class="text-blue-100 text-xl mb-12 max-w-2xl mx-auto">التسجيل مجاني، والفرص لا محدودة. انضم إلينا اليوم وغير مستقبلك المالي.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" style="background-color: #FFD700 !important; color: #051d2e !important;" class="px-10 py-5 font-black text-lg rounded-2xl shadow-xl hover:shadow-2xl hover:shadow-yellow-700/20 transform hover:-translate-y-1 transition-all">
                        أنشئ حساب مجاني
                    </a>
                    <a href="{{ route('login') }}" class="px-10 py-5 bg-transparent border-2 border-white/20 hover:bg-white/10 text-white font-bold text-lg rounded-2xl transition-all">
                        لديك حساب بالفعل؟
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-8">
            <x-application-logo />
            <div class="flex flex-wrap justify-center gap-8 text-sm font-medium text-gray-500">
                <a href="#" class="hover:text-primary-900 transition">الشروط والأحكام</a>
                <a href="#" class="hover:text-primary-900 transition">سياسة الخصوصية</a>
                <a href="#" class="hover:text-primary-900 transition">تواصل معنا</a>
            </div>
            <p class="text-sm text-gray-400">© 2026 منصة {{ config('app.name', 'حليف') }}. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

</body>

</html>