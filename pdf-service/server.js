const express = require('express');
const puppeteer = require('puppeteer');
const app = express();

app.get('/generate', async (req, res) => {
    const { url } = req.query;
    if (!url) return res.status(400).send('Missing url');

    // أمان: امنع الروابط الخارجية
    const allowedHosts = ['localhost', '127.0.0.1', 'your-domain.com'];
    try {
        const parsed = new URL(url);
        if (!allowedHosts.some(host => parsed.hostname.includes(host))) {
            return res.status(403).send('Forbidden');
        }
    } catch (e) {
        return res.status(400).send('Invalid URL');
    }

    let browser;
    try {
        browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'], headless: 'new' });
        const page = await browser.newPage();
        await page.setViewport({ width: 1200, height: 1600 });
        await page.goto(url, { waitUntil: 'networkidle0' });
        const pdf = await page.pdf({ format: 'A4', printBackground: true });
        await browser.close();
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', 'attachment; filename="cv.pdf"');
        res.send(pdf);
    } catch (err) {
        if (browser) await browser.close();
        res.status(500).send('PDF error');
    }
});

app.listen(3001, () => console.log('PDF service on port 3001'));