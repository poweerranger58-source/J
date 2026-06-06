<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Checker Pro - Proxy Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { max-width: 1600px; margin: 0 auto; }
        
        /* Header */
        .header {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 { font-size: 2em; color: #333; }
        .header h1 i { color: #0070ba; margin-right: 10px; }
        .header p { color: #666; font-size: 0.9em; margin-top: 5px; }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 0.85em; text-transform: uppercase; color: #888; margin-bottom: 10px; }
        .stat-card .number { font-size: 2.5em; font-weight: 800; }
        .stat-card.total .number { color: #3b82f6; }
        .stat-card.approved .number { color: #10b981; }
        .stat-card.declined .number { color: #ef4444; }
        .stat-card.pending .number { color: #f59e0b; }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 20px;
        }
        
        /* Input Section */
        .input-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: monospace;
            font-size: 13px;
            resize: vertical;
        }
        
        textarea:focus { outline: none; border-color: #667eea; }
        
        .thread-control {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .thread-control input {
            flex: 1;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
        }
        
        /* Proxy Section */
        .proxy-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        
        .proxy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .proxy-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .proxy-toggle input {
            width: 50px;
            height: 25px;
            appearance: none;
            background: #ccc;
            border-radius: 25px;
            position: relative;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .proxy-toggle input:checked {
            background: #10b981;
        }
        
        .proxy-toggle input::before {
            content: '';
            width: 21px;
            height: 21px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 3px;
            transition: 0.3s;
        }
        
        .proxy-toggle input:checked::before {
            left: 26px;
        }
        
        .proxy-list {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .proxy-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .proxy-status {
            font-size: 12px;
            margin-top: 10px;
            padding: 8px;
            background: #e5e7eb;
            border-radius: 5px;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 10px;
        }
        
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s;
        }
        
        .status-text { text-align: center; margin-top: 10px; font-size: 14px; color: #666; }
        
        /* Results Section */
        .results-section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            flex-wrap: wrap;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background: none;
            font-weight: 600;
            color: #666;
        }
        
        .tab.active { color: #667eea; border-bottom: 2px solid #667eea; }
        
        .results-container { max-height: 600px; overflow-y: auto; }
        
        .result-item {
            background: #f9fafb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid;
            font-size: 13px;
        }
        
        .result-item.approved { border-left-color: #10b981; }
        .result-item.declined { border-left-color: #ef4444; }
        .result-item.pending { border-left-color: #f59e0b; }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-weight: 600;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .result-cc {
            font-family: monospace;
            font-size: 14px;
            font-weight: 700;
        }
        
        .result-status {
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 11px;
        }
        
        .result-details {
            margin-top: 10px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 8px;
            font-family: monospace;
            font-size: 11px;
            word-break: break-all;
        }
        
        .result-bin {
            background: #e5e7eb;
            padding: 8px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .main-content { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fab fa-paypal"></i> PayPal Checker Pro - Proxy Edition</h1>
            <p>Bulk CC Checker | Multi-Thread | Proxy Support | Auto BIN Lookup | Response Details</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card total"><h3><i class="fas fa-list"></i> Total</h3><div class="number" id="totalCount">0</div></div>
            <div class="stat-card approved"><h3><i class="fas fa-check-circle"></i> Aprovados</h3><div class="number" id="approvedCount">0</div></div>
            <div class="stat-card declined"><h3><i class="fas fa-times-circle"></i> Reprovados</h3><div class="number" id="declinedCount">0</div></div>
            <div class="stat-card pending"><h3><i class="fas fa-clock"></i> 3DS</h3><div class="number" id="pendingCount">0</div></div>
        </div>

        <div class="main-content">
            <div class="input-section">
                <div class="input-group">
                    <label><i class="fas fa-credit-card"></i> Lista de CCs</label>
                    <textarea id="ccList" rows="8" placeholder="FORMATO: NUMERO|MES|ANO|CVV&#10;&#10;Exemplos:&#10;4147202062065055|08|29|956&#10;4242424242424242|12|28|123&#10;5555555555554444|01|30|737"></textarea>
                </div>

                <!-- PROXY SECTION -->
                <div class="proxy-section">
                    <div class="proxy-header">
                        <label><i class="fas fa-network-wired"></i> <strong>Proxy Configuration</strong></label>
                        <div class="proxy-toggle">
                            <span>Off</span>
                            <input type="checkbox" id="proxyEnabled" onchange="toggleProxy()">
                            <span>On</span>
                        </div>
                    </div>
                    
                    <textarea id="proxyList" rows="3" class="proxy-list" placeholder="FORMATO: IP:PORT:USER:PASS ou IP:PORT&#10;&#10;Exemplos:&#10;192.168.1.1:8080&#10;proxy.example.com:3128:user:pass"></textarea>
                    
                    <div class="proxy-actions">
                        <button class="btn-small" onclick="testProxies()"><i class="fas fa-check-circle"></i> Testar Proxys</button>
                        <button class="btn-small" onclick="loadProxyExample()"><i class="fas fa-file-import"></i> Exemplo</button>
                        <button class="btn-small" onclick="clearProxies()"><i class="fas fa-trash"></i> Limpar</button>
                    </div>
                    
                    <div id="proxyStatus" class="proxy-status">
                        <i class="fas fa-info-circle"></i> Nenhum proxy testado
                    </div>
                    <div id="proxyCount" style="font-size: 11px; margin-top: 5px; color: #666;"></div>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-tachometer-alt"></i> Threads (1-10)</label>
                    <div class="thread-control">
                        <input type="number" id="threads" min="1" max="10" value="5">
                        <span>Proxy rotation a cada <input type="number" id="rotateAfter" min="1" max="20" value="5" style="width: 50px;"> checks</span>
                    </div>
                </div>

                <button class="btn btn-primary" id="startBtn" onclick="startChecking()">
                    <i class="fas fa-play"></i> Iniciar Verificação
                </button>
                <button class="btn btn-danger" id="stopBtn" onclick="stopChecking()" disabled>
                    <i class="fas fa-stop"></i> Parar
                </button>
                <button class="btn btn-warning" id="clearBtn" onclick="clearResults()">
                    <i class="fas fa-trash-alt"></i> Limpar Resultados
                </button>

                <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
                <div class="status-text" id="statusText">Aguardando inicio...</div>
            </div>

            <div class="results-section">
                <div class="tabs">
                    <button class="tab active" onclick="showTab('approved')">✅ Aprovados (<span id="approvedTabCount">0</span>)</button>
                    <button class="tab" onclick="showTab('declined')">❌ Reprovados (<span id="declinedTabCount">0</span>)</button>
                    <button class="tab" onclick="showTab('pending')">⚠️ 3DS (<span id="pendingTabCount">0</span>)</button>
                    <button class="tab" onclick="showTab('all')">📋 Todos (<span id="allTabCount">0</span>)</button>
                </div>
                <div class="results-container" id="resultsContainer">
                    <div style="text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>
                        Nenhum resultado ainda
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let isRunning = false;
        let results = { approved: [], declined: [], pending: [] };
        let activeTab = 'approved';
        let activeProxies = [];
        let currentProxyIndex = 0;
        let requestsSinceRotate = 0;

        function toggleProxy() {
            const enabled = document.getElementById('proxyEnabled').checked;
            document.getElementById('proxyList').disabled = !enabled;
            document.getElementById('rotateAfter').disabled = !enabled;
            if (!enabled) {
                document.getElementById('proxyStatus').innerHTML = '<i class="fas fa-info-circle"></i> Proxy desabilitado - usando IP direto';
            }
        }

        async function testProxies() {
            const proxyText = document.getElementById('proxyList').value;
            if (!proxyText.trim()) {
                alert('Adicione proxys primeiro!');
                return;
            }
            
            document.getElementById('proxyStatus').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testando proxys...';
            
            const proxies = proxyText.split('\n').filter(p => p.trim());
            let working = [];
            
            for (let proxy of proxies) {
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'test_proxy', proxy: proxy.trim() })
                    });
                    const result = await response.json();
                    if (result.status === 'working') {
                        working.push(proxy.trim());
                    }
                } catch(e) {}
            }
            
            activeProxies = working;
            document.getElementById('proxyStatus').innerHTML = `<i class="fas fa-check-circle"></i> ${activeProxies.length}/${proxies.length} proxys funcionando`;
            document.getElementById('proxyCount').innerHTML = `📡 Proxys ativos: ${activeProxies.length}`;
            
            if (activeProxies.length > 0) {
                currentProxyIndex = 0;
                document.getElementById('proxyEnabled').checked = true;
                toggleProxy();
            }
        }

        function loadProxyExample() {
            document.getElementById('proxyList').value = '192.168.1.1:8080\nproxy.example.com:3128:user:pass\n45.67.89.100:8080:user123:pass456';
        }

        function clearProxies() {
            document.getElementById('proxyList').value = '';
            activeProxies = [];
            document.getElementById('proxyStatus').innerHTML = '<i class="fas fa-info-circle"></i> Nenhum proxy testado';
            document.getElementById('proxyCount').innerHTML = '';
        }

        function getNextProxy() {
            if (!document.getElementById('proxyEnabled').checked || activeProxies.length === 0) {
                return null;
            }
            
            const rotateAfter = parseInt(document.getElementById('rotateAfter').value);
            if (requestsSinceRotate >= rotateAfter) {
                currentProxyIndex = (currentProxyIndex + 1) % activeProxies.length;
                requestsSinceRotate = 0;
            }
            
            requestsSinceRotate++;
            return activeProxies[currentProxyIndex];
        }

        function showTab(tab) {
            activeTab = tab;
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            renderResults();
        }

        function updateStats() {
            const total = results.approved.length + results.declined.length + results.pending.length;
            document.getElementById('totalCount').innerText = total;
            document.getElementById('approvedCount').innerText = results.approved.length;
            document.getElementById('declinedCount').innerText = results.declined.length;
            document.getElementById('pendingCount').innerText = results.pending.length;
            
            document.getElementById('approvedTabCount').innerText = results.approved.length;
            document.getElementById('declinedTabCount').innerText = results.declined.length;
            document.getElementById('pendingTabCount').innerText = results.pending.length;
            document.getElementById('allTabCount').innerText = total;
        }

        function clearResults() {
            results = { approved: [], declined: [], pending: [] };
            updateStats();
            renderResults();
        }

        function renderResults() {
            let items = [];
            if (activeTab === 'approved') items = results.approved;
            else if (activeTab === 'declined') items = results.declined;
            else if (activeTab === 'pending') items = results.pending;
            else items = [...results.approved, ...results.declined, ...results.pending];

            const container = document.getElementById('resultsContainer');
            
            if (items.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;"><i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>Nenhum resultado</div>';
                return;
            }

            container.innerHTML = items.map(item => `
                <div class="result-item ${item.status}">
                    <div class="result-header">
                        <span class="result-cc"><strong>${item.card_full}</strong> | ${item.bin_info.brand} ${item.bin_info.country_flag}</span>
                        <span class="result-status">${item.result}</span>
                    </div>
                    <div class="result-details">
                        <strong>📝 Response completa:</strong><br>
                        ${item.full_response || item.result}
                    </div>
                    <div class="result-bin">
                        <i class="fas fa-chart-line"></i> 💳 ${item.bin_info.bank} | ${item.bin_info.level} | ${item.bin_info.type}<br>
                        <i class="fas fa-globe"></i> BIN: ${item.bin} | País: ${item.bin_info.country}
                        ${item.proxy_used ? `<br><i class="fas fa-network-wired"></i> Proxy: ${item.proxy_used}` : ''}
                    </div>
                </div>
            `).join('');
        }

        async function startChecking() {
            const ccList = document.getElementById('ccList').value.trim();
            if (!ccList) {
                alert('Adicione CCs');
                return;
            }

            const threads = parseInt(document.getElementById('threads').value);
            isRunning = true;
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            
            results = { approved: [], declined: [], pending: [] };
            updateStats();
            
            const cards = ccList.split('\n').filter(l => l.trim());
            let processed = 0;
            let activeRequests = 0;
            
            document.getElementById('statusText').innerHTML = `Processando ${cards.length} cartões...`;
            requestsSinceRotate = 0;

            function processNext() {
                if (!isRunning) return;
                if (processed >= cards.length) {
                    if (activeRequests === 0) finishChecking();
                    return;
                }
                
                const card = cards[processed];
                processed++;
                activeRequests++;
                
                const proxy = getNextProxy();
                
                fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ card: card, proxy: proxy })
                })
                .then(res => res.json())
                .then(data => {
                    let status = 'declined';
                    if (data.result.includes('APPROVED') || data.result.includes('CHARGE')) {
                        status = 'approved';
                    } else if (data.result.includes('3DS')) {
                        status = 'pending';
                    }
                    
                    data.status = status;
                    data.card_full = card.split('|')[0];
                    data.proxy_used = proxy;
                    results[status].unshift(data);
                    updateStats();
                    
                    if (activeTab === 'all' || activeTab === status) {
                        renderResults();
                    }
                    
                    const percent = (processed / cards.length) * 100;
                    document.getElementById('progressFill').style.width = percent + '%';
                    document.getElementById('statusText').innerHTML = `${processed}/${cards.length} (${Math.round(percent)}%) | Proxy: ${proxy ? 'via proxy' : 'direto'}`;
                })
                .catch(error => {
                    console.error('Error:', error);
                })
                .finally(() => {
                    activeRequests--;
                    if (isRunning) processNext();
                    else if (activeRequests === 0) finishChecking();
                });
            }
            
            for (let i = 0; i < Math.min(threads, cards.length); i++) processNext();
        }
        
        function finishChecking() {
            isRunning = false;
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
            document.getElementById('statusText').innerHTML = '✅ Verificação concluída!';
            document.getElementById('progressFill').style.width = '100%';
        }
        
        function stopChecking() {
            isRunning = false;
            document.getElementById('statusText').innerHTML = '⏹️ Verificação interrompida';
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
        }
    </script>
</body>
</html>
