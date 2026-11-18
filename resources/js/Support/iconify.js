export function getIconifyLogoUrl(nameStr) {
    if (!nameStr) return null;
    const name = String(nameStr).toLowerCase().trim();

    if (
        name.includes('department of health') ||
        name === 'doh' ||
        name === 'fhsis'
    ) {
        return '/repository-media/portfolio/organizations/department-of-health.png';
    }
    if (name.includes('surigao del norte state university') || name.includes('snsu')) {
        return '/repository-media/portfolio/organizations/snsu.png';
    }
    if (name.includes('dict')) {
        return '/repository-media/portfolio/organizations/dict.svg';
    }

    const iconMap = {
        'linkedin': '/images/brands/iconify/logos--linkedin-icon.svg',
        'power bi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'powerbi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'microsoft power bi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'microsoft': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft office': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft 365': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft excel': '/images/brands/iconify/mdi--microsoft-excel--color-217346.svg',
        'excel': '/images/brands/iconify/mdi--microsoft-excel--color-217346.svg',
        'canva': '/images/brands/iconify/devicon--canva.svg',
        'adobe': '/images/brands/iconify/logos--adobe-icon.svg',
        'adobe creative cloud': '/images/brands/iconify/cib--adobe-creative-cloud--color-DA1F26.svg',
        'adobe creative suite': '/images/brands/iconify/cib--adobe-creative-cloud--color-DA1F26.svg',
        'adobe photoshop': '/images/brands/iconify/logos--adobe-photoshop.svg',
        'adobe illustrator': '/images/brands/iconify/logos--adobe-illustrator.svg',
        'adobe indesign': '/images/brands/iconify/logos--adobe-indesign.svg',
        'adobe premiere pro': '/images/brands/iconify/logos--adobe-premiere.svg',
        'laravel': '/images/brands/iconify/logos--laravel.svg',
        'php': '/images/brands/iconify/logos--php.svg',
        'vue.js': '/images/brands/iconify/logos--vue.svg',
        'vue': '/images/brands/iconify/logos--vue.svg',
        'inertia.js': '/images/brands/iconify/simple-icons--inertia.svg',
        'inertia': '/images/brands/iconify/simple-icons--inertia.svg',
        'codeigniter': '/images/brands/iconify/simple-icons--codeigniter.svg',
        'python': '/images/brands/iconify/logos--python.svg',
        'docker': '/images/brands/iconify/logos--docker-icon.svg',
        'tailwind css': '/images/brands/iconify/logos--tailwindcss-icon.svg',
        'tailwindcss': '/images/brands/iconify/logos--tailwindcss-icon.svg',
        'bootstrap': '/images/brands/iconify/logos--bootstrap.svg',
        'mysql': '/images/brands/iconify/logos--mysql-icon.svg',
        'postgresql': '/images/brands/iconify/logos--postgresql.svg',
        'sqlite': '/images/brands/iconify/logos--sqlite.svg',
        'sql': '/images/brands/iconify/lucide--database.svg',
        'sql databases': '/images/brands/iconify/lucide--database.svg',
        'emr': '/images/brands/iconify/lucide--heart-pulse.svg',
        'git': '/images/brands/iconify/logos--git-icon.svg',
        'github': '/images/brands/iconify/logos--github-icon.svg',
        'gitlab': '/images/brands/iconify/logos--gitlab.svg',
        'figma': '/images/brands/iconify/logos--figma.svg',
        'mikrotik': '/images/brands/iconify/simple-icons--mikrotik.svg',
        'routeros': '/images/brands/iconify/simple-icons--mikrotik.svg',
        'cisco': '/images/brands/iconify/simple-icons--cisco.svg',
        'n8n': '/images/brands/iconify/simple-icons--n8n.svg',
        'zapier': '/images/brands/iconify/logos--zapier-icon.svg',
        'javascript': '/images/brands/iconify/logos--javascript.svg',
        'typescript': '/images/brands/iconify/logos--typescript-icon.svg',
        'html5': '/images/brands/iconify/logos--html-5.svg',
        'html': '/images/brands/iconify/logos--html-5.svg',
        'css3': '/images/brands/iconify/logos--css-3.svg',
        'css': '/images/brands/iconify/logos--css-3.svg',
        'vite': '/images/brands/iconify/logos--vitejs.svg',
        'nginx': '/images/brands/iconify/logos--nginx.svg',
        'apache': '/images/brands/iconify/logos--apache.svg',
        'linux': '/images/brands/iconify/logos--tux.svg',
        'ubuntu': '/images/brands/iconify/logos--ubuntu.svg',
        'cpanel': '/images/brands/iconify/logos--cpanel.svg',
        'notion': '/images/brands/iconify/logos--notion-icon.svg',
        'google workspace': '/images/brands/iconify/logos--google-icon.svg',
        'google': '/images/brands/iconify/logos--google-icon.svg',
        'gemini': '/images/brands/iconify/logos--google-gemini.svg',
        'google gemini': '/images/brands/iconify/logos--google-gemini.svg',
        'claude': '/images/brands/iconify/simple-icons--anthropic.svg',
        'health informatics': '/images/brands/iconify/lucide--heart-pulse.svg',
        'responsible ai': '/images/brands/iconify/lucide--shield-check.svg',
        'digital transformation': '/images/brands/iconify/lucide--workflow.svg',
        'data literacy': '/images/brands/iconify/lucide--chart-column.svg',
        'health it': '/images/brands/iconify/lucide--heart-pulse.svg',
        'networking': '/images/brands/iconify/lucide--network.svg',
        'chatgpt': '/images/brands/iconify/logos--openai-icon.svg',
        'openai': '/images/brands/iconify/logos--openai-icon.svg',
        'gpt-4': '/images/brands/iconify/logos--openai-icon.svg',
        'gptzero': 'https://gptzero.me/favicon.ico',
        'hugging face': '/images/brands/iconify/logos--hugging-face-icon.svg',
        'notebooklm': '/images/brands/iconify/logos--google-icon.svg',
        'postman': '/images/brands/iconify/logos--postman-icon.svg',
        'redis': '/images/brands/iconify/logos--redis.svg',
        'pandas': '/images/brands/iconify/logos--pandas-icon.svg',
        'numpy': '/images/brands/iconify/logos--numpy.svg',
        'scikit-learn': '/images/brands/iconify/simple-icons--scikitlearn.svg',
        'credly': '/images/brands/iconify/simple-icons--credly.svg',
        'shopify': '/images/brands/iconify/logos--shopify.svg',
        'gohighlevel': '/images/brands/gohighlevel.png',
        'facebook ads': '/images/brands/iconify/logos--facebook.svg',
        'facebook': '/images/brands/iconify/logos--facebook.svg',
        'sap': '/images/brands/iconify/simple-icons--sap.svg',
        'cybersecurity': '/images/brands/iconify/lucide--shield-check.svg',
        'vulnerability assessment': '/images/brands/iconify/lucide--shield-alert.svg',
        'vulnerability audit': '/images/brands/iconify/lucide--shield-alert.svg',
        'data privacy': '/images/brands/iconify/lucide--shield-check.svg',
        'firewall': '/images/brands/iconify/lucide--shield.svg',
        'firewall hardening': '/images/brands/iconify/lucide--shield.svg',
        'ssl': '/images/brands/iconify/lucide--lock.svg',
        'systems analysis': '/images/brands/iconify/lucide--workflow.svg',
        'agile': '/images/brands/iconify/lucide--repeat-2.svg',
        'lean six sigma': '/images/brands/iconify/lucide--trending-up.svg',
        'bigquery': '/images/brands/iconify/simple-icons--googlebigquery.svg',
    };

    return iconMap[name] || null;
}
