<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manga Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 2.5em;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px 20px 0 0;
            padding: 20px 20px 0 20px;
            gap: 10px;
            margin-bottom: 0;
            backdrop-filter: blur(10px);
        }

        .tab {
            padding: 12px 24px;
            border: none;
            border-radius: 12px 12px 0 0;
            cursor: pointer;
            font-weight: 600;
            background: transparent;
            color: #666;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .admin-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0 0 20px 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
            color: #333;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ff7675, #e84393);
            color: white;
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-edit:hover, .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .search-box {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            width: 300px;
            transition: border-color 0.3s ease;
        }

        .search-box:focus {
            outline: none;
            border-color: #667eea;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .data-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.3s ease;
            font-size: 13px;
        }

        .data-table tr:hover {
            background-color: #f8f9ff;
        }

        .cover-img {
            width: 50px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                width: 100%;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table th, .data-table td {
                padding: 8px 6px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Manga Admin Dashboard</h1>
            <p>Real-time manga management connected to mobile app</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-number" id="totalSeries">-</div>
                <div>Total Series</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📖</div>
                <div class="stat-number" id="totalComics">-</div>
                <div>Total Comics</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-number" id="totalUsers">-</div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number" id="totalProgress">-</div>
                <div>Reading Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❤️</div>
                <div class="stat-number" id="totalFavorites">-</div>
                <div>Total Favorites</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('series')">📚 Series</button>
            <button class="tab" onclick="showTab('comics')">📖 Comics</button>
            <button class="tab" onclick="showTab('users')">👥 Users</button>
            <button class="tab" onclick="showTab('progress')">📊 Reading Progress</button>
            <button class="tab" onclick="showTab('favorites')">❤️ Favorites</button>
        </div>

        <div class="admin-panel">
            <div class="alert alert-success" id="successAlert"></div>
            <div class="alert alert-error" id="errorAlert"></div>

            <!-- Series Tab -->
            <div id="series" class="tab-content active">
                <div class="controls">
                    <h2>📚 Series Management</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="text" id="seriesSearch" class="search-box" placeholder="🔍 Search series...">
                        <button class="btn btn-primary" onclick="openModal('series', 'add')">➕ Add Series</button>
                        <button class="btn btn-primary" onclick="refreshData('series')">🔄 Refresh</button>
                    </div>
                </div>
                <div id="seriesLoading" class="loading">
                    <div class="spinner"></div>
                    Loading series data...
                </div>
                <table class="data-table" id="seriesTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Comics</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="seriesTableBody"></tbody>
                </table>
            </div>

            <!-- Comics Tab -->
            <div id="comics" class="tab-content">
                <div class="controls">
                    <h2>📖 Comics Management</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="text" id="comicsSearch" class="search-box" placeholder="🔍 Search comics...">
                        <button class="btn btn-primary" onclick="openModal('comics', 'add')">➕ Add Comic</button>
                        <button class="btn btn-primary" onclick="refreshData('comics')">🔄 Refresh</button>
                    </div>
                </div>
                <div id="comicsLoading" class="loading">
                    <div class="spinner"></div>
                    Loading comics data...
                </div>
                <table class="data-table" id="comicsTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cover</th>
                            <th>Series</th>
                            <th>Chapter</th>
                            <th>Title</th>
                            <th>Pages</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="comicsTableBody"></tbody>
                </table>
            </div>

            <!-- Users Tab -->
            <div id="users" class="tab-content">
                <div class="controls">
                    <h2>👥 Users Management</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="text" id="usersSearch" class="search-box" placeholder="🔍 Search users...">
                        <button class="btn btn-primary" onclick="refreshData('users')">🔄 Refresh</button>
                    </div>
                </div>
                <div id="usersLoading" class="loading">
                    <div class="spinner"></div>
                    Loading users data...
                </div>
                <table class="data-table" id="usersTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Verified</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody"></tbody>
                </table>
            </div>

            <!-- Reading Progress Tab -->
            <div id="progress" class="tab-content">
                <div class="controls">
                    <h2>📊 Reading Progress</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="text" id="progressSearch" class="search-box" placeholder="🔍 Search progress...">
                        <button class="btn btn-primary" onclick="refreshData('progress')">🔄 Refresh</button>
                    </div>
                </div>
                <div id="progressLoading" class="loading">
                    <div class="spinner"></div>
                    Loading progress data...
                </div>
                <table class="data-table" id="progressTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Comic</th>
                            <th>Current Page</th>
                            <th>Total Pages</th>
                            <th>Progress</th>
                            <th>Last Read</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="progressTableBody"></tbody>
                </table>
            </div>

            <!-- Favorites Tab -->
            <div id="favorites" class="tab-content">
                <div class="controls">
                    <h2>❤️ Favorites Management</h2>
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <input type="text" id="favoritesSearch" class="search-box" placeholder="🔍 Search favorites...">
                        <button class="btn btn-primary" onclick="refreshData('favorites')">🔄 Refresh</button>
                    </div>
                </div>
                <div id="favoritesLoading" class="loading">
                    <div class="spinner"></div>
                    Loading favorites data...
                </div>
                <table class="data-table" id="favoritesTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Series</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="favoritesTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit -->
    <div id="dataModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Data</h2>
            <form id="dataForm">
                <div id="formFields"></div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">💾 Save</button>
                    <button type="button" class="btn" onclick="closeModal()" style="background: #ccc;">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // API Configuration
        const API_BASE = '/api';
        let currentTab = 'series';
        let editingId = null;
        let editingType = null;
        
        // Data storage
        let seriesData = [];
        let comicsData = [];
        let usersData = [];
        let progressData = [];
        let favoritesData = [];

        // Get CSRF token
        function getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // API Request helper - TANPA AUTH untuk testing
        async function apiRequest(endpoint, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            };

            try {
                const response = await fetch(API_BASE + endpoint, {
                    ...defaultOptions,
                    ...options,
                    headers: { ...defaultOptions.headers, ...options.headers }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return await response.json();
            } catch (error) {
                console.error('API Request failed:', error);
                // Return sample data for demo if API fails
                return getSampleData(endpoint);
            }
        }

        // Sample data fallback
        function getSampleData(endpoint) {
            if (endpoint.includes('/series')) {
                return {
                    success: true,
                    data: [
                        { id: 1, title: 'Attack on Titan', description: 'Humanity fights against giant titans to survive', cover_image: 'attackontitan_cover.gif', status: 'completed', created_at: '2024-01-15', comics_count: 4, cover_url: '/storage/series/attackontitan_cover.gif' },
                        { id: 2, title: 'Kage no Jitsuryokusha', description: 'The Eminence in Shadow - A story about a boy who dreams of becoming a shadow broker', cover_image: 'Kage_no_jitsuryokusha_cover.gif', status: 'ongoing', created_at: '2024-01-14', comics_count: 4, cover_url: '/storage/series/Kage_no_jitsuryokusha_cover.gif' },
                        { id: 3, title: 'Kaoru Hana wa Rin to Saku', description: 'A beautiful story about love and friendship', cover_image: 'Kaoru Hana wa Rin to Saku_cover.gif', status: 'ongoing', created_at: '2024-01-13', comics_count: 5, cover_url: '/storage/series/Kaoru Hana wa Rin to Saku_cover.gif' },
                        { id: 4, title: 'One Piece', description: 'The greatest adventure story of pirates searching for One Piece treasure', cover_image: 'onepiece_cover.gif', status: 'ongoing', created_at: '2024-01-12', comics_count: 17, cover_url: '/storage/series/onepiece_cover.gif' }
                    ]
                };
            } else if (endpoint.includes('/comics')) {
                return {
                    success: true,
                    data: [
                        { id: 1, series_id: 1, series: { title: 'Attack on Titan' }, chapter_number: '01', title: 'Chapter 1', cover_image: 'attackontitan/Chapter 01/04.jpg', page_count: 24, created_at: '2024-01-15', cover_url: '/storage/comics/attackontitan/Chapter 01/04.jpg' },
                        { id: 2, series_id: 1, series: { title: 'Attack on Titan' }, chapter_number: '02', title: 'Chapter 2', cover_image: 'attackontitan/Chapter 02/04.jpg', page_count: 25, created_at: '2024-01-16', cover_url: '/storage/comics/attackontitan/Chapter 02/04.jpg' },
                        { id: 3, series_id: 2, series: { title: 'Kage no Jitsuryokusha' }, chapter_number: '001', title: 'I Am Atomic', cover_image: 'Kage_no_jitsuryokusha/Chapter 001/004.jpg', page_count: 39, created_at: '2024-01-17', cover_url: '/storage/comics/Kage_no_jitsuryokusha/Chapter 001/004.jpg' },
                        { id: 4, series_id: 2, series: { title: 'Kage no Jitsuryokusha' }, chapter_number: '002', title: 'The Secret Organization', cover_image: 'Kage_no_jitsuryokusha/Chapter 002/004.jpg', page_count: 35, created_at: '2024-01-18', cover_url: '/storage/comics/Kage_no_jitsuryokusha/Chapter 002/004.jpg' }
                    ]
                };
            } else if (endpoint.includes('/favorites')) {
                return {
                    success: true,
                    data: [
                        { id: 1, title: 'Attack on Titan', created_at: '2024-01-15', user: { name: 'John Doe' } },
                        { id: 2, title: 'One Piece', created_at: '2024-01-16', user: { name: 'Jane Smith' } }
                    ]
                };
            } else if (endpoint.includes('/reading-progress')) {
                return {
                    success: true,
                    data: [
                        { id: 1, user: { name: 'John Doe' }, comic: { title: 'Attack on Titan Ch.1' }, current_page: 15, total_pages: 24, progress_percentage: 62.5, last_read_at: '2024-01-16T14:30:00Z' },
                        { id: 2, user: { name: 'Jane Smith' }, comic: { title: 'Kage no Jitsuryokusha Ch.1' }, current_page: 20, total_pages: 39, progress_percentage: 51.3, last_read_at: '2024-01-17T09:15:00Z' }
                    ]
                };
            }
            return { success: false, data: [] };
        }

        // Load data from API
        async function loadData(type) {
            try {
                showLoading(type, true);
                let data;

                switch(type) {
                    case 'series':
                        data = await apiRequest('/series');
                        seriesData = data.data || [];
                        break;
                    case 'comics':
                        data = await apiRequest('/comics');
                        comicsData = data.data || [];
                        break;
                    case 'users':
                        // This would need a users endpoint
                        usersData = []; // Placeholder
                        break;
                    case 'progress':
                        data = await apiRequest('/reading-progress');
                        progressData = data.data || [];
                        break;
                    case 'favorites':
                        data = await apiRequest('/favorites');
                        favoritesData = data.data || [];
                        break;
                }

                renderTable(type);
                updateStats();
                
            } catch (error) {
                console.error('Error loading data:', error);
                showAlert(`Failed to load ${type}: ${error.message}`, 'error');
            } finally {
                showLoading(type, false);
            }
        }

        // Show/hide loading
        function showLoading(type, show) {
            const loading = document.getElementById(type + 'Loading');
            const table = document.getElementById(type + 'Table');
            
            if (loading && table) {
                loading.style.display = show ? 'block' : 'none';
                table.style.display = show ? 'none' : 'table';
            }
        }

        // Update statistics
        function updateStats() {
            document.getElementById('totalSeries').textContent = seriesData.length;
            document.getElementById('totalComics').textContent = comicsData.length;
            document.getElementById('totalUsers').textContent = usersData.length;
            document.getElementById('totalProgress').textContent = progressData.length;
            document.getElementById('totalFavorites').textContent = favoritesData.length;
        }

        // Show tab
        function showTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');

            currentTab = tabName;
            
            // Load data if not loaded
            if (getData(tabName).length === 0) {
                loadData(tabName);
            }
        }

        // Get data by type
        function getData(type) {
            switch(type) {
                case 'series': return seriesData;
                case 'comics': return comicsData;
                case 'users': return usersData;
                case 'progress': return progressData;
                case 'favorites': return favoritesData;
                default: return [];
            }
        }

        // Render table
        function renderTable(type, filteredData = null) {
            const data = filteredData || getData(type);
            const tbody = document.getElementById(type + 'TableBody');
            
            if (!tbody) return;
            
            tbody.innerHTML = '';

            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = getRowHTML(type, item);
                tbody.appendChild(row);
            });
        }

        // Get row HTML by type
        function getRowHTML(type, item) {
            switch(type) {
                case 'series':
                    return `
                        <td>${item.id}</td>
                        <td><img src="${item.cover_url || '/storage/series/' + item.cover_image}" class="cover-img" onerror="this.src='/images/no-image.png'"></td>
                        <td><strong>${item.title}</strong></td>
                        <td>${(item.description || '').substring(0, 50)}...</td>
                        <td><span class="status-badge" style="background: ${getStatusColor(item.status)}">${(item.status || 'unknown').toUpperCase()}</span></td>
                        <td>${item.comics_count || 0} chapters</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>
                            <button class="btn btn-edit" onclick="editItem('series', ${item.id})">✏️ Edit</button>
                            <button class="btn btn-delete" onclick="deleteItem('series', ${item.id})">🗑️ Delete</button>
                        </td>
                    `;
                case 'comics':
                    return `
                        <td>${item.id}</td>
                        <td><img src="${item.cover_url || '/storage/comics/' + item.cover_image}" class="cover-img" onerror="this.src='/images/no-image.png'"></td>
                        <td>${item.series?.title || 'Unknown'}</td>
                        <td>Ch. ${item.chapter_number}</td>
                        <td><strong>${item.title}</strong></td>
                        <td>${item.page_count} pages</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>
                            <button class="btn btn-edit" onclick="editItem('comics', ${item.id})">✏️ Edit</button>
                            <button class="btn btn-delete" onclick="deleteItem('comics', ${item.id})">🗑️ Delete</button>
                        </td>
                    `;
                case 'progress':
                    return `
                        <td>${item.id}</td>
                        <td>${item.user?.name || 'Unknown'}</td>
                        <td>${item.comic?.title || 'Unknown'}</td>
                        <td>${item.current_page}</td>
                        <td>${item.total_pages}</td>
                        <td>
                            <div style="background: #f0f0f0; border-radius: 10px; height: 20px; position: relative;">
                                <div style="background: linear-gradient(135deg, #667eea, #764ba2); height: 100%; border-radius: 10px; width: ${item.progress_percentage || 0}%;"></div>
                                <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; font-weight: bold; color: #333;">${(item.progress_percentage || 0).toFixed(1)}%</span>
                            </div>
                        </td>
                        <td>${formatDateTime(item.last_read_at)}</td>
                        <td>
                            <button class="btn btn-delete" onclick="deleteItem('progress', ${item.id})">🗑️ Delete</button>
                        </td>
                    `;
                case 'favorites':
                    return `
                        <td>${item.id}</td>
                        <td>${item.user?.name || 'Unknown'}</td>
                        <td>${item.title || item.series?.title || 'Unknown'}</td>
                        <td>${formatDate(item.created_at)}</td>
                        <td>
                            <button class="btn btn-delete" onclick="deleteItem('favorites', ${item.id})">🗑️ Remove</button>
                        </td>
                    `;
                default: return '';
            }
        }

        // Get status color
        function getStatusColor(status) {
            switch(status) {
                case 'ongoing': return '#27ae60';
                case 'completed': return '#3498db';
                case 'hiatus': return '#f39c12';
                case 'cancelled': return '#e74c3c';
                default: return '#95a5a6';
            }
        }

        // Format date
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString();
        }

        // Format datetime
        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleString();
        }

        // Setup search functionality
        function setupSearch() {
            ['series', 'comics', 'users', 'progress', 'favorites'].forEach(type => {
                const searchInput = document.getElementById(type + 'Search');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const data = getData(type);
                        const filtered = data.filter(item => {
                            const searchableText = JSON.stringify(item).toLowerCase();
                            return searchableText.includes(searchTerm);
                        });
                        renderTable(type, filtered);
                    });
                }
            });
        }

        // Refresh data
        function refreshData(type) {
            loadData(type);
        }

        // Open modal for add/edit
        function openModal(type, mode, id = null) {
            const modal = document.getElementById('dataModal');
            const modalTitle = document.getElementById('modalTitle');
            const formFields = document.getElementById('formFields');
            
            editingType = type;
            editingId = mode === 'edit' ? id : null;
            
            modalTitle.textContent = mode === 'add' ? `Add New ${type.charAt(0).toUpperCase() + type.slice(1, -1)}` : `Edit ${type.charAt(0).toUpperCase() + type.slice(1, -1)}`;
            
            formFields.innerHTML = getFormFields(type, mode, id);
            
            modal.style.display = 'block';
        }

        // Get form fields by type
        function getFormFields(type, mode, id = null) {
            let item = null;
            if (mode === 'edit' && id) {
                const data = getData(type);
                item = data.find(d => d.id === id);
            }

            switch(type) {
                case 'series':
                    return `
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" id="title" name="title" value="${item ? item.title : ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" rows="3" required>${item ? item.description : ''}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cover_image">Cover Image:</label>
                                <input type="text" id="cover_image" name="cover_image" value="${item ? item.cover_image : ''}" placeholder="cover.gif" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Status:</label>
                                <select id="status" name="status" required>
                                    <option value="ongoing" ${item && item.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                                    <option value="completed" ${item && item.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="hiatus" ${item && item.status === 'hiatus' ? 'selected' : ''}>Hiatus</option>
                                    <option value="cancelled" ${item && item.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    `;
                case 'comics':
                    const seriesOptions = seriesData.map(s => `<option value="${s.id}" ${item && item.series_id === s.id ? 'selected' : ''}>${s.title}</option>`).join('');
                    return `
                        <div class="form-group">
                            <label for="series_id">Series:</label>
                            <select id="series_id" name="series_id" required>
                                <option value="">Select Series</option>
                                ${seriesOptions}
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="chapter_number">Chapter Number:</label>
                                <input type="text" id="chapter_number" name="chapter_number" value="${item ? item.chapter_number : ''}" placeholder="001" required>
                            </div>
                            <div class="form-group">
                                <label for="page_count">Page Count:</label>
                                <input type="number" id="page_count" name="page_count" value="${item ? item.page_count : ''}" min="1" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" id="title" name="title" value="${item ? item.title : ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="cover_image">Cover Image Path:</label>
                            <input type="text" id="cover_image" name="cover_image" value="${item ? item.cover_image : ''}" placeholder="series/Chapter 001/004.jpg" required>
                        </div>
                        <div class="form-group">
                            <label for="pages">Pages (JSON Array):</label>
                            <textarea id="pages" name="pages" rows="3" placeholder='["page1.jpg", "page2.jpg"]' required>${item ? JSON.stringify(item.pages) : ''}</textarea>
                        </div>
                    `;
                default:
                    return '<p>Form not available for this type</p>';
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('dataModal').style.display = 'none';
            editingId = null;
            editingType = null;
        }

        // Edit item
        function editItem(type, id) {
            openModal(type, 'edit', id);
        }

        // Delete item
        async function deleteItem(type, id) {
            if (!confirm(`Are you sure you want to delete this ${type.slice(0, -1)}?`)) {
                return;
            }

            try {
                let endpoint = '';
                switch(type) {
                    case 'series':
                        endpoint = `/admin/series/${id}`;
                        break;
                    case 'comics':
                        endpoint = `/admin/comics/${id}`;
                        break;
                    case 'favorites':
                        // For favorites, we need to find the series_id and toggle it
                        const favorite = favoritesData.find(f => f.id === id);
                        if (favorite && favorite.series_id) {
                            endpoint = `/favorites/${favorite.series_id}/toggle`;
                        }
                        break;
                    default:
                        throw new Error('Delete not implemented for this type');
                }

                if (endpoint) {
                    await apiRequest(endpoint, { method: 'DELETE' });
                    showAlert(`${type.charAt(0).toUpperCase() + type.slice(1, -1)} deleted successfully!`, 'success');
                    loadData(type); // Reload data
                }
            } catch (error) {
                console.error('Error deleting item:', error);
                showAlert(`Failed to delete ${type.slice(0, -1)}: ${error.message}`, 'error');
            }
        }

        // Handle form submission
        document.getElementById('dataForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to object
            for (let [key, value] of formData.entries()) {
                if (key === 'pages') {
                    try {
                        data[key] = JSON.parse(value);
                    } catch (e) {
                        showAlert('Invalid JSON format for pages', 'error');
                        return;
                    }
                } else {
                    data[key] = value;
                }
            }

            try {
                let endpoint = '';
                let method = 'POST';

                if (editingId) {
                    // Update existing
                    method = 'PUT';
                    switch(editingType) {
                        case 'series':
                            endpoint = `/admin/series/${editingId}`;
                            break;
                        case 'comics':
                            endpoint = `/admin/comics/${editingId}`;
                            break;
                    }
                } else {
                    // Add new
                    switch(editingType) {
                        case 'series':
                            endpoint = '/admin/series';
                            break;
                        case 'comics':
                            endpoint = '/admin/comics';
                            break;
                    }
                }

                if (endpoint) {
                    const response = await apiRequest(endpoint, {
                        method: method,
                        body: JSON.stringify(data)
                    });

                    showAlert(`${editingType.charAt(0).toUpperCase() + editingType.slice(1, -1)} ${editingId ? 'updated' : 'added'} successfully!`, 'success');
                    loadData(editingType); // Reload data
                    closeModal();
                }
            } catch (error) {
                console.error('Error saving data:', error);
                showAlert(`Failed to save ${editingType.slice(0, -1)}: ${error.message}`, 'error');
            }
        });

        // Show alert
        function showAlert(message, type) {
            const alertElement = document.getElementById(type === 'success' ? 'successAlert' : 'errorAlert');
            alertElement.textContent = message;
            alertElement.style.display = 'block';
            
            setTimeout(() => {
                alertElement.style.display = 'none';
            }, 5000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('dataModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Initialize dashboard when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupSearch();
            loadData('series'); // Load initial data
        });
    </script>
</body>
</html>