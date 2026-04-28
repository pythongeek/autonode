<?php defined( 'ABSPATH' ) || exit; ?>
<div class="amp-wrap">
    <div class="amp-header">
        <div class="amp-header-left">
            <h1 class="amp-page-title"> n8n Starter Workflows</h1>
            <p>One-click copyable n8n workflows specifically designed for AutoNode WP.</p>
        </div>
    </div>

    <div class="amp-grid-2">
        <!-- Master Template: AI Agent Starter -->
        <div class="amp-card amp-template-card amp-template-master">
            <div class="amp-card-header">
                <h3>🚀 Master AI Agent Starter</h3>
                <button class="amp-btn amp-btn-primary amp-copy-btn" data-template="master">Copy Master Workflow</button>
            </div>
            <div class="amp-card-body">
                <p>The ultimate production-ready workflow. Includes a centralized Config Hub, WP connection health checks, and a One-Shot Publish demonstration with SEO and Media.</p>
                <div class="amp-workflow-preview">
                    <span class="node config">Config Hub</span> <span class="node webhook">Manual Trigger</span> <span class="node http">Status Check</span> <span class="node http">One-Shot Publish</span>
                </div>
            </div>
            <textarea id="tpl-master" style="display:none;">{"nodes":[{"parameters":{"jsCode":"/**\n * 🚀 AUTONODE MASTER CONFIG HUB\n * Centralize your automation settings here for easy maintenance.\n */\n\nconst now = new Date().toISOString();\n\nreturn [{\n  json: {\n    // --- WordPress & AutoNode ---\n    wp_base_url: 'https://your-site.com', \n    wp_api: 'https://your-site.com/wp-json/autonode/v1',\n    wp_api_key: 'ampcm_YOUR_64_CHAR_KEY_HERE',\n    \n    // --- AI & External APIs (Optional) ---\n    gemini_api_key: 'AIzaSy...',\n    google_cse_id: '...',\n    google_api_key: '...',\n    supabase_url: 'https://your-project.supabase.co',\n    supabase_anon_key: '...',\n    \n    // --- Workflow Logic ---\n    images_per_article: 2,\n    default_category: 1,\n    default_status: 'draft',\n    version: 'AutoNode-V4.2',\n    batch_id: 'batch_' + Date.now(),\n    workflow_start: now\n  }\n}];"},"id":"config-hub","name":"Workflow Config Hub","type":"n8n-nodes-base.code","typeVersion":2,"position":[-600,300],"alwaysOutputData":true},{"parameters":{},"id":"trigger","name":"Manual Trigger","type":"n8n-nodes-base.manualTrigger","typeVersion":1,"position":[-800,300]},{"parameters":{"method":"GET","url":"={{ $node[\"Workflow Config Hub\"].json.wp_api }}/status","sendHeaders":true,"headerParameters":{"parameters":[{"name":"X-API-Key","value":"={{ $node[\"Workflow Config Hub\"].json.wp_api_key }}"}]}},"id":"status-check","name":"Check WP Connection","type":"n8n-nodes-base.httpRequest","typeVersion":4.1,"position":[-300,300]},{"parameters":{"method":"POST","url":"={{ $node[\"Workflow Config Hub\"].json.wp_api }}/bulk/oneshot","sendHeaders":true,"headerParameters":{"parameters":[{"name":"X-API-Key","value":"={{ $node[\"Workflow Config Hub\"].json.wp_api_key }}"}]},"sendBody":true,"specifyBody":"json","jsonBody":"={\n  \"title\": \"Hello from n8n & AutoNode!\",\n  \"content\": \"<p>This post was created automatically using the Master One-Shot endpoint.</p>\",\n  \"status\": \"{{ $node[\\\"Workflow Config Hub\\\"].json.default_status }}\",\n  \"featured_image_url\": \"https://images.unsplash.com/photo-1485827404703-89b55fcc595e\",\n  \"seo\": {\n    \"focus_keyword\": \"autonode n8n\",\n    \"title\": \"AutoNode + n8n Integration Guide\",\n    \"description\": \"Learn how to automate WordPress with n8n using AutoNode WP.\"\n  }\n}","options":{}},"id":"oneshot-publish","name":"One-Shot Publish","type":"n8n-nodes-base.httpRequest","typeVersion":4.1,"position":[0,300]},{"parameters":{"content":"## 🛠️ AutoNode Master Config & Setup\n\n1. **Enter Credentials**: Open the **'Workflow Config Hub'** node and enter your API keys.\n2. **WP Base URL**: Use your full site URL (e.g., `https://example.com`).\n3. **AutoNode Key**: Generate this in **AutoNode WP > API Keys**.\n4. **Prerequisites**: Ensure Rank Math SEO is installed and Permalinks are set to 'Post name'.","height":260,"width":500},"id":"doc-header","name":"Setup Guide","type":"n8n-nodes-base.stickyNote","typeVersion":1,"position":[-600,0]}],"connections":{"Manual Trigger":{"main":[[{"node":"Workflow Config Hub","type":"main","index":0}]]},"Workflow Config Hub":{"main":[[{"node":"Check WP Connection","type":"main","index":0}]]},"Check WP Connection":{"main":[[{"node":"One-Shot Publish","type":"main","index":0}]]}}}</textarea>
        </div>

        <!-- Template 1: Basic One-Shot Publish -->
        <div class="amp-card amp-template-card">
            <div class="amp-card-header">
                <h3>Post with Rank Math SEO</h3>
                <button class="amp-btn amp-btn-primary amp-copy-btn" data-template="oneshot">Copy Workflow</button>
            </div>
            <div class="amp-card-body">
                <p>Creates a live post, sideloads a featured image, and sets all Rank Math metadata in a single HTTP node.</p>
                <div class="amp-workflow-preview">
                    <span class="node webhook">Webhook</span>  <span class="node http">HTTP Request (AutoNode WP)</span>
                </div>
            </div>
            <textarea id="tpl-oneshot" style="display:none;">{"nodes":[{"parameters":{"httpMethod":"POST","path":"publish-post","options":{}},"name":"Webhook","type":"n8n-nodes-base.webhook","typeVersion":1,"position":[250,300]},{"parameters":{"method":"POST","url":"={{\\$env.WP_URL}}/wp-json/autonode/v1/bulk/oneshot","sendHeaders":true,"headerParameters":{"parameters":[{"name":"Authorization","value":"Bearer ={{\\$env.WP_API_KEY}}"}]},"sendBody":true,"specifyBody":"json","jsonBody":"={\n  \"title\": \"{{$json.body.title}}\",\n  \"content\": \"{{$json.body.content}}\",\n  \"status\": \"publish\",\n  \"featured_image_url\": \"{{$json.body.image_url}}\",\n  \"seo\": {\n    \"focus_keyword\": \"{{$json.body.keyword}}\",\n    \"title\": \"{{$json.body.title}} | Blog\"\n  }\n}","options":{}},"name":"AutoNode WP","type":"n8n-nodes-base.httpRequest","typeVersion":4.1,"position":[450,300]}],"connections":{"Webhook":{"main":[[{"node":"AutoNode WP","type":"main","index":0}]]}}}</textarea>
        </div>

        <!-- Template 2: Outgoing Webhook Receiver -->
        <div class="amp-card amp-template-card">
            <div class="amp-card-header">
                <h3>Receive WP Webhooks</h3>
                <button class="amp-btn amp-btn-primary amp-copy-btn" data-template="receiver">Copy Workflow</button>
            </div>
            <div class="amp-card-body">
                <p>Catches our optimized flattened JSON payloads when a post is published, ready for processing.</p>
                <div class="amp-workflow-preview">
                    <span class="node webhook">Webhook</span>  <span class="node set">Filter Payload</span>
                </div>
            </div>
            <textarea id="tpl-receiver" style="display:none;">{"nodes":[{"parameters":{"httpMethod":"POST","path":"wp-webhook-sync","options":{}},"name":"Webhook","type":"n8n-nodes-base.webhook","typeVersion":1,"position":[250,300]},{"parameters":{"values":{"string":[{"name":"Title","value":"={{$json.body.post.post_title}}"},{"name":"Keyword","value":"={{$json.body.post.seo.focus_keyword}}"}],"number":[{"name":"ID","value":"={{$json.body.post.ID}}"}]},"options":{}},"name":"Set","type":"n8n-nodes-base.set","typeVersion":2,"position":[450,300]}],"connections":{"Webhook":{"main":[[{"node":"Set","type":"main","index":0}]]}}}</textarea>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.amp-copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = 'tpl-' + this.getAttribute('data-template');
            const textarea = document.getElementById(templateId);
            if(textarea) {
                navigator.clipboard.writeText(textarea.value).then(() => {
                    const originalText = this.innerText;
                    this.innerText = 'Copied!';
                    this.classList.add('amp-btn-success');
                    setTimeout(() => {
                        this.innerText = originalText;
                        this.classList.remove('amp-btn-success');
                    }, 2000);
                });
            }
        });
    });
});
</script>
<style>
.amp-template-card { border-left: 4px solid #EA4B71; } /* n8n vibrant pink */
.amp-template-master { border-left-color: var(--amp-primary); }
.amp-workflow-preview { margin-top:15px; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; display:flex; align-items:center; gap:10px; font-size:12px; }
.amp-workflow-preview .node { padding: 4px 10px; border-radius: 4px; font-weight:600; color:#fff; }
.amp-workflow-preview .webhook { background: #1C2630; border: 1px solid #7348e3; color: #fff;}
.amp-workflow-preview .http { background: #1C2630; border: 1px solid #48e367; }
.amp-workflow-preview .set { background: #1C2630; border: 1px solid #2bcffa; }
.amp-workflow-preview .config { background: #1C2630; border: 1px solid var(--amp-primary); }
.amp-btn-success { background: #48e367 !important; border-color:#48e367 !important; color:#000 !important; }
</style>

