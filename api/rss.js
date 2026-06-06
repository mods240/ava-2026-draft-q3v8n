// Vercel Serverless Function: RSSプロキシ
// 同一ドメインからRSSを取得するため、CORS問題を完全に回避
export default async function handler(req, res) {
  // キャッシュ制御: 5分間キャッシュ、1分間はstale-while-revalidate
  res.setHeader('Cache-Control', 's-maxage=300, stale-while-revalidate=60');
  res.setHeader('Access-Control-Allow-Origin', '*');
  
  const rssUrl = req.query.url;
  
  if (!rssUrl) {
    return res.status(400).json({ error: 'url parameter is required' });
  }
  
  // はてなブログのRSSのみ許可（セキュリティ対策）
  if (!rssUrl.includes('hatenablog.com/')) {
    return res.status(403).json({ error: 'Only hatenablog.com RSS feeds are allowed' });
  }
  
  try {
    const response = await fetch(rssUrl, {
      headers: {
        'User-Agent': 'AVA-Website-RSS-Reader/1.0'
      }
    });
    
    if (!response.ok) {
      throw new Error(`RSS fetch failed: ${response.status}`);
    }
    
    const xml = await response.text();
    
    // XMLをそのまま返す
    res.setHeader('Content-Type', 'application/xml; charset=utf-8');
    return res.status(200).send(xml);
  } catch (error) {
    console.error('RSS proxy error:', error);
    return res.status(500).json({ error: error.message });
  }
}
