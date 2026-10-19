import instaloader
import sys
import json
import re

def scrape_ig(url):
    try:
        match = re.search(r'instagram\.com/p/([^/?]+)', url)
        if not match:
            print(json.dumps({"error": "Invalid Instagram post URL"}))
            return
            
        shortcode = match.group(1)
        
        L = instaloader.Instaloader(
            download_pictures=False,
            download_video_thumbnails=False,
            download_geotags=False,
            download_comments=False,
            save_metadata=False,
            compress_json=False
        )
        
        post = instaloader.Post.from_shortcode(L.context, shortcode)
        
        image_urls = []
        if post.typename == 'GraphSidecar':
            for node in post.get_sidecar_nodes():
                image_urls.append(node.display_url)
        else:
            image_urls.append(post.url)
            
        data = {
            "caption": post.caption,
            "owner": post.owner_username,
            "date": str(post.date),
            "likes": post.likes,
            "url": url,
            "image_urls": image_urls
        }
        print(json.dumps({"success": True, "data": data}))
    except Exception as e:
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    if len(sys.argv) > 1:
        scrape_ig(sys.argv[1])
    else:
        scrape_ig("https://www.instagram.com/p/DeBC0xHk32-/?utm_source=ig_web_copy_link&stkn=MzRlODBiNWFlZA==")
