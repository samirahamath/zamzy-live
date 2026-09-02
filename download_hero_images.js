const https = require('https');
const fs = require('fs');
const path = require('path');

const images = [
  // 01: Full-Stack SaaS & Web Platforms
  { name: 'image-01.jpg', url: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=640&q=85' },
  // 02: Performance Ads & Google/Meta Marketing
  { name: 'image-02.jpg', url: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=640&q=85' },
  // 03: AI & Workflow Automation
  { name: 'image-03.jpg', url: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=640&q=85' },
  // 04: Local SEO & Top 3 Google Rankings
  { name: 'image-04.jpg', url: 'https://images.unsplash.com/photo-1571786256017-aee7a0c009b6?auto=format&fit=crop&w=640&q=85' },
  // 05: Mobile App Development (Flutter / iOS)
  { name: 'image-05.jpg', url: 'https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=640&q=85' },
  // 06: Social Media Marketing & Creative Branding
  { name: 'image-06.jpg', url: 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=640&q=85' },
  // 07: Cloud Infrastructure & AWS DevOps
  { name: 'image-07.jpg', url: 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=640&q=85' },
  // 08: CRO, Funnels & Conversion Tracking
  { name: 'image-08.jpg', url: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=640&q=85' },
  // 09: Enterprise ERP & Database Architecture
  { name: 'image-09.jpg', url: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=640&q=85' },
  // 10: Revenue Telemetry & Growth ROI Dashboards
  { name: 'image-10.jpg', url: 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=640&q=85' }
];

function download(url, dest) {
  return new Promise((resolve, reject) => {
    https.get(url, (res) => {
      if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
        return download(res.headers.location, dest).then(resolve).catch(reject);
      }
      if (res.statusCode !== 200) {
        return reject(new Error(`Failed with status ${res.statusCode}`));
      }
      const file = fs.createWriteStream(dest);
      res.pipe(file);
      file.on('finish', () => {
        file.close(() => resolve(dest));
      });
    }).on('error', reject);
  });
}

async function run() {
  const dir = path.join(__dirname, 'images');
  for (const img of images) {
    const dest = path.join(dir, img.name);
    try {
      await download(img.url, dest);
      console.log(`Downloaded ${img.name}`);
    } catch (err) {
      console.error(`Error downloading ${img.name}:`, err.message);
    }
  }
}

run();
