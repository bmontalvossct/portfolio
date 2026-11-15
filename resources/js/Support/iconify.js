export function getIconifyLogoUrl(nameStr) {
    if (!nameStr) return null;
    const name = String(nameStr).toLowerCase().trim();

    if (
        name.includes('department of health') ||
        name === 'doh' ||
        name === 'fhsis' ||
        name.includes('emr') ||
        name.includes('electronic medical record')
    ) {
        return '/storage/portfolio/organizations/department-of-health.png';
    }
    if (name.includes('surigao del norte state university') || name.includes('snsu')) {
        return '/storage/portfolio/organizations/snsu.png';
    }
    if (name.includes('dict')) {
        return '/storage/portfolio/organizations/dict.svg';
    }

    const iconMap = {
        'linkedin': 'https://api.iconify.design/logos/linkedin-icon.svg',
        'power bi': 'https://api.iconify.design/logos/microsoft-power-bi.svg',
        'powerbi': 'https://api.iconify.design/logos/microsoft-power-bi.svg',
        'microsoft power bi': 'https://api.iconify.design/logos/microsoft-power-bi.svg',
        'microsoft': 'https://api.iconify.design/logos/microsoft-icon.svg',
        'microsoft office': 'https://api.iconify.design/logos/microsoft-icon.svg',
        'microsoft 365': 'https://api.iconify.design/logos/microsoft-icon.svg',
        'microsoft excel': 'https://api.iconify.design/mdi/microsoft-excel.svg?color=%23217346',
        'excel': 'https://api.iconify.design/mdi/microsoft-excel.svg?color=%23217346',
        'canva': 'https://api.iconify.design/devicon/canva.svg',
        'adobe': 'https://api.iconify.design/logos/adobe-icon.svg',
        'adobe creative cloud': 'https://api.iconify.design/cib/adobe-creative-cloud.svg?color=%23DA1F26',
        'adobe creative suite': 'https://api.iconify.design/cib/adobe-creative-cloud.svg?color=%23DA1F26',
        'adobe photoshop': 'https://api.iconify.design/logos/adobe-photoshop.svg',
        'adobe illustrator': 'https://api.iconify.design/logos/adobe-illustrator.svg',
        'adobe indesign': 'https://api.iconify.design/logos/adobe-indesign.svg',
        'adobe premiere pro': 'https://api.iconify.design/logos/adobe-premiere.svg',
        'laravel': 'https://api.iconify.design/logos/laravel.svg',
        'php': 'https://api.iconify.design/logos/php.svg',
        'vue.js': 'https://api.iconify.design/logos/vue.svg',
        'vue': 'https://api.iconify.design/logos/vue.svg',
        'inertia.js': 'https://api.iconify.design/simple-icons/inertia.svg',
        'inertia': 'https://api.iconify.design/simple-icons/inertia.svg',
        'codeigniter': 'https://api.iconify.design/simple-icons/codeigniter.svg',
        'python': 'https://api.iconify.design/logos/python.svg',
        'docker': 'https://api.iconify.design/logos/docker-icon.svg',
        'tailwind css': 'https://api.iconify.design/logos/tailwindcss-icon.svg',
        'tailwindcss': 'https://api.iconify.design/logos/tailwindcss-icon.svg',
        'bootstrap': 'https://api.iconify.design/logos/bootstrap.svg',
        'mysql': 'https://api.iconify.design/logos/mysql-icon.svg',
        'postgresql': 'https://api.iconify.design/logos/postgresql.svg',
        'sqlite': 'https://api.iconify.design/logos/sqlite.svg',
        'sql': 'https://api.iconify.design/logos/mysql-icon.svg',
        'sql databases': 'https://api.iconify.design/logos/mysql-icon.svg',
        'git': 'https://api.iconify.design/logos/git-icon.svg',
        'github': 'https://api.iconify.design/logos/github-icon.svg',
        'gitlab': 'https://api.iconify.design/logos/gitlab.svg',
        'figma': 'https://api.iconify.design/logos/figma.svg',
        'mikrotik': 'https://api.iconify.design/simple-icons/mikrotik.svg',
        'routeros': 'https://api.iconify.design/simple-icons/mikrotik.svg',
        'cisco': 'https://api.iconify.design/simple-icons/cisco.svg',
        'n8n': 'https://api.iconify.design/simple-icons/n8n.svg',
        'zapier': 'https://api.iconify.design/logos/zapier-icon.svg',
        'javascript': 'https://api.iconify.design/logos/javascript.svg',
        'typescript': 'https://api.iconify.design/logos/typescript-icon.svg',
        'html5': 'https://api.iconify.design/logos/html-5.svg',
        'html': 'https://api.iconify.design/logos/html-5.svg',
        'css3': 'https://api.iconify.design/logos/css-3.svg',
        'css': 'https://api.iconify.design/logos/css-3.svg',
        'vite': 'https://api.iconify.design/logos/vitejs.svg',
        'nginx': 'https://api.iconify.design/logos/nginx.svg',
        'apache': 'https://api.iconify.design/logos/apache.svg',
        'linux': 'https://api.iconify.design/logos/tux.svg',
        'ubuntu': 'https://api.iconify.design/logos/ubuntu.svg',
        'cpanel': 'https://api.iconify.design/logos/cpanel.svg',
        'notion': 'https://api.iconify.design/logos/notion-icon.svg',
        'google workspace': 'https://api.iconify.design/logos/google-icon.svg',
        'google': 'https://api.iconify.design/logos/google-icon.svg',
        'gemini': 'https://api.iconify.design/logos/google-gemini.svg',
        'google gemini': 'https://api.iconify.design/logos/google-gemini.svg',
        'claude': 'https://api.iconify.design/simple-icons/anthropic.svg',
        'health informatics': 'https://api.iconify.design/lucide/heart-pulse.svg',
        'responsible ai': 'https://api.iconify.design/lucide/shield-check.svg',
        'digital transformation': 'https://api.iconify.design/lucide/workflow.svg',
        'data literacy': 'https://api.iconify.design/lucide/chart-column.svg',
        'health it': 'https://api.iconify.design/lucide/heart-pulse.svg',
        'networking': 'https://api.iconify.design/lucide/network.svg',
        'chatgpt': 'https://api.iconify.design/logos/openai-icon.svg',
        'openai': 'https://api.iconify.design/logos/openai-icon.svg',
        'gpt-4': 'https://api.iconify.design/logos/openai-icon.svg',
        'gptzero': 'https://gptzero.me/favicon.ico',
        'hugging face': 'https://api.iconify.design/logos/hugging-face-icon.svg',
        'notebooklm': 'https://api.iconify.design/logos/google-icon.svg',
        'postman': 'https://api.iconify.design/logos/postman-icon.svg',
        'redis': 'https://api.iconify.design/logos/redis.svg',
        'pandas': 'https://api.iconify.design/logos/pandas-icon.svg',
        'numpy': 'https://api.iconify.design/logos/numpy.svg',
        'scikit-learn': 'https://api.iconify.design/simple-icons/scikitlearn.svg',
        'credly': 'https://api.iconify.design/simple-icons/credly.svg',
        'shopify': 'https://api.iconify.design/logos/shopify.svg',
        'gohighlevel': '/images/brands/gohighlevel.png',
        'facebook ads': 'https://api.iconify.design/logos/facebook.svg',
        'facebook': 'https://api.iconify.design/logos/facebook.svg',
        'sap': 'https://api.iconify.design/simple-icons/sap.svg',
        'cybersecurity': 'https://api.iconify.design/lucide/shield-check.svg',
        'vulnerability assessment': 'https://api.iconify.design/lucide/shield-alert.svg',
        'vulnerability audit': 'https://api.iconify.design/lucide/shield-alert.svg',
        'data privacy': 'https://api.iconify.design/lucide/shield-check.svg',
        'firewall': 'https://api.iconify.design/lucide/shield.svg',
        'firewall hardening': 'https://api.iconify.design/lucide/shield.svg',
        'ssl': 'https://api.iconify.design/lucide/lock.svg',
        'systems analysis': 'https://api.iconify.design/lucide/workflow.svg',
        'agile': 'https://api.iconify.design/lucide/repeat-2.svg',
        'lean six sigma': 'https://api.iconify.design/lucide/trending-up.svg',
        'bigquery': 'https://api.iconify.design/simple-icons/googlebigquery.svg',
    };

    return iconMap[name] || null;
}
