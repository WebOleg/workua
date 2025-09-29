<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">URL Shortener</h1>
            <p class="text-gray-600">Create short, memorable links in seconds</p>
        </div>

        <!-- Main Form -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <form id="shortenForm">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Long URL *
                    </label>
                    <input 
                        type="url" 
                        id="url" 
                        required
                        placeholder="https://example.com/very/long/url"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Custom Code (optional)
                        </label>
                        <input 
                            type="text" 
                            id="customCode"
                            placeholder="mycustom"
                            pattern="[a-zA-Z0-9]{6,10}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">6-10 characters, letters and numbers only</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Expiration (minutes, optional)
                        </label>
                        <input 
                            type="number" 
                            id="ttl"
                            placeholder="60"
                            min="1"
                            max="525600"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">Leave empty for no expiration</p>
                    </div>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-colors"
                >
                    Shorten URL
                </button>
            </form>

            <!-- Result -->
            <div id="result" class="hidden mt-8 p-6 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="text-lg font-semibold text-green-900 mb-4">Success!</h3>
                <div class="flex items-center gap-2 mb-4">
                    <input 
                        type="text" 
                        id="shortUrl" 
                        readonly
                        class="flex-1 px-4 py-2 bg-white border border-green-300 rounded-lg"
                    >
                    <button 
                        onclick="copyToClipboard()"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        Copy
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <span class="font-medium">Short Code:</span>
                        <span id="shortCode"></span>
                    </div>
                    <div>
                        <span class="font-medium">Expires:</span>
                        <span id="expiresAt"></span>
                    </div>
                </div>
            </div>

            <!-- Error -->
            <div id="error" class="hidden mt-8 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"></div>
        </div>

        <!-- Recent Links -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Links</h2>
            <div id="recentLinks" class="space-y-4"></div>
        </div>
    </div>

    <script>
        const recentLinks = JSON.parse(localStorage.getItem('recentLinks') || '[]');

        document.getElementById('shortenForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const url = document.getElementById('url').value;
            const customCode = document.getElementById('customCode').value;
            const ttl = document.getElementById('ttl').value;

            const data = { url };
            if (customCode) data.custom_code = customCode;
            if (ttl) data.ttl_minutes = parseInt(ttl);

            try {
                const response = await fetch('/api/links', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok) {
                    showResult(result.data);
                    addToRecent(result.data);
                    document.getElementById('shortenForm').reset();
                } else {
                    showError(result.message || 'Failed to create short link');
                }
            } catch (error) {
                showError('Network error. Please try again.');
            }
        });

        function showResult(data) {
            document.getElementById('result').classList.remove('hidden');
            document.getElementById('error').classList.add('hidden');
            document.getElementById('shortUrl').value = data.short_url;
            document.getElementById('shortCode').textContent = data.short_code;
            document.getElementById('expiresAt').textContent = data.expires_at ? new Date(data.expires_at).toLocaleString() : 'Never';
        }

        function showError(message) {
            document.getElementById('error').textContent = message;
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('result').classList.add('hidden');
        }

        function copyToClipboard() {
            const input = document.getElementById('shortUrl');
            input.select();
            document.execCommand('copy');
            alert('Copied to clipboard!');
        }

        function addToRecent(link) {
            recentLinks.unshift(link);
            if (recentLinks.length > 5) recentLinks.pop();
            localStorage.setItem('recentLinks', JSON.stringify(recentLinks));
            renderRecentLinks();
        }

        async function renderRecentLinks() {
            const container = document.getElementById('recentLinks');
            if (recentLinks.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No recent links</p>';
                return;
            }

            // Fetch fresh data for each link
            const promises = recentLinks.map(link => 
                fetch(`/api/links/${link.short_code}`)
                    .then(r => r.json())
                    .then(data => data.success ? data.data : link)
                    .catch(() => link)
            );

            const freshLinks = await Promise.all(promises);

            container.innerHTML = freshLinks.map(link => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${link.original_url}</p>
                        <p class="text-sm text-gray-500">
                            <a href="${link.short_url}" target="_blank" class="text-blue-600 hover:underline">${link.short_url}</a>
                        </p>
                    </div>
                    <div class="ml-4 text-sm">
                        <span class="font-semibold text-green-600">${link.visits_count || 0}</span>
                        <span class="text-gray-500"> visits</span>
                    </div>
                </div>
            `).join('');
        }

        // Initial render and refresh every 5 seconds
        renderRecentLinks();
        setInterval(renderRecentLinks, 5000);
    </script>
</body>
</html>
