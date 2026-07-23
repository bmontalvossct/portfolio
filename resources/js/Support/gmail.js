export function gmailComposeUrl(email, options = {}) {
    const parameters = [
        ['view', 'cm'],
        ['fs', '1'],
        ['to', email],
        ['su', options.subject],
        ['body', options.body],
    ]
        .filter(([, value]) => value)
        .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
        .join('&');

    return `https://mail.google.com/mail/?${parameters}`;
}
