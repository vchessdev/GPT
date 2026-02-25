<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài Viết - DevDA Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .post-single {
            background: var(--bg-primary);
            padding: 40px;
            border-radius: var(--radius-md);
            margin: 30px 0;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        .post-thumbnail {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            display: block;
        }

        .post-single h1 {
            margin-bottom: 15px;
            font-size: 36px;
            color: var(--text-primary);
        }

        .post-meta {
            color: var(--text-secondary);
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .post-meta strong {
            color: var(--text-primary);
        }

        .post-content {
            font-size: 16px;
            line-height: 1.8;
            margin: 30px 0;
            color: var(--text-primary);
        }

        .post-content img {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            margin: 20px 0;
        }

        .post-tags {
            margin: 20px 0;
        }

        .tag {
            display: inline-block;
            background: var(--bg-secondary);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            margin-right: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: var(--transition);
        }

        .tag:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .vote-section {
            display: flex;
            gap: 15px;
            margin: 30px 0;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }

        .vote-btn {
            flex: 1;
            padding: 10px 20px;
            border: 1px solid var(--border);
            background: var(--bg-primary);
            color: var(--text-primary);
            cursor: pointer;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            font-weight: 600;
        }

        .vote-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--bg-secondary);
        }

        .vote-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .comments-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid var(--border);
        }

        .comments-section h2 {
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .comment-form {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: var(--radius-md);
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .comment-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-family: inherit;
            margin-bottom: 10px;
            background: var(--bg-primary);
            color: var(--text-primary);
            resize: vertical;
        }

        .comment-form textarea::placeholder {
            color: var(--text-light);
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .comment-item {
            background: var(--bg-secondary);
            padding: 15px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }

        .comment-author {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .comment-date {
            font-size: 12px;
            color: var(--text-light);
        }

        .comment-content {
            margin: 10px 0;
            color: var(--text-secondary);
        }

        .reply-btn {
            font-size: 12px;
            color: #0066cc;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .reply-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="with-sidebar">
    <aside class="sidebar">
        <div class="logo">🚀 <span>DevDA</span></div>
        <nav class="sidebar-nav">
            <a href="<?php echo BASE_URL; ?>" class="sidebar-nav-item">
                <span>🏠</span>
                <span class="text">Trang Chủ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>?page=posts" class="sidebar-nav-item">
                <span>📚</span>
                <span class="text">Bài Viết</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/search.php" class="sidebar-nav-item">
                <span>🔍</span>
                <span class="text">Tìm Kiếm</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/profile.php" class="sidebar-nav-item" id="profileLink" style="display:none;">
                <span>👤</span>
                <span class="text">Hồ Sơ</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/post-form.php" class="sidebar-nav-item" id="postLink" style="display:none;">
                <span>✍️</span>
                <span class="text">Đăng Bài</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/leaderboard.php" class="sidebar-nav-item">
                <span>🏆</span>
                <span class="text">Xếp Hạng</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/admin/" class="sidebar-nav-item" id="adminLink" style="display:none;">
                <span>⚙️</span>
                <span class="text">Admin</span>
            </a>
            <div style="border-top: 1px solid var(--border); margin: 12px 0;"></div>
            <button id="darkModeBtn" class="sidebar-nav-item" onclick="toggleDarkMode()" style="background: none; border: none; cursor: pointer; width: 100%; text-align: left;">
                <span>🌙</span>
                <span class="text">Dark Mode</span>
            </button>
            <a href="#" id="loginLink" class="sidebar-nav-item" style="display:none;">
                <span>🔐</span>
                <span class="text">Đăng Nhập</span>
            </a>
            <a href="#" id="logoutBtn" class="sidebar-nav-item" style="color: var(--danger); display:none;">
                <span>🚪</span>
                <span class="text">Đăng Xuất</span>
            </a>
        </nav>
    </aside>

    <main class="container">
        <div class="post-single">
            <div id="postContent">
                <p>Đang tải...</p>
            </div>
        </div>
    </main>


    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    <script>
        const postId = new URLSearchParams(window.location.search).get('id');

        if (!postId) {
            document.getElementById('postContent').innerHTML = '<p>Không tìm thấy bài viết</p>';
        } else {
            loadPost();
        }

        async function loadPost() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/posts.php?action=get&id=' + postId);
                const data = await res.json();

                if (!data.post) {
                    document.getElementById('postContent').innerHTML = '<p>Bài viết không tồn tại</p>';
                    return;
                }

                const post = data.post;
                const author = data.author;

                let tagsHtml = '';
                if (post.tags && post.tags.length > 0) {
                    tagsHtml = '<div class="post-tags">' +
                        post.tags.map(tag => `<span class="tag">${tag}</span>`).join('') +
                        '</div>';
                }

                let html = `
                    ${post.thumbnail ? `<img src="${post.thumbnail}" alt="${post.title}" class="post-thumbnail">` : ''}
                    <h1>${post.title}</h1>
                    <div class="post-meta">
                        <strong>Tác giả:</strong> ${author.username} | 
                        <strong>Ngày:</strong> ${post.created_at} | 
                        <strong>Lượt xem:</strong> ${post.views} | 
                        <strong>Danh mục:</strong> ${post.category}
                    </div>
                    ${tagsHtml}
                    <div class="post-content">
                        ${post.content.replace(/\n/g, '<br>')}
                    </div>
                    <div class="vote-section">
                        <button class="vote-btn" id="likeBtn">👍 Like <span id="likeCount">${data.likes}</span></button>
                        <button class="vote-btn" id="dislikeBtn">👎 Dislike <span id="dislikeCount">${data.dislikes}</span></button>
                    </div>
                `;

                // Check login status
                const authRes = await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=check');
                const authData = await authRes.json();

                if (authData.loggedIn) {
                    document.getElementById('authLinks').style.display = 'none';
                    document.getElementById('userLinks').style.display = 'block';
                    document.getElementById('username').textContent = authData.user.username;

                    // Load votes
                    const votesRes = await fetch('<?php echo BASE_URL; ?>/api/votes.php?action=getVotes&post_id=' + postId);
                    const votesData = await votesRes.json();
                    
                    if (votesData.userVote === 'like') {
                        document.getElementById('likeBtn').classList.add('active');
                    } else if (votesData.userVote === 'dislike') {
                        document.getElementById('dislikeBtn').classList.add('active');
                    }
                }

                html += `
                    <div class="comments-section">
                        <h2>Bình Luận (${data.commentCount})</h2>
                        ${authData.loggedIn ? `
                            <div class="comment-form">
                                <textarea id="commentInput" placeholder="Bình luận của bạn..."></textarea>
                                <button class="btn btn-primary" onclick="postComment()">Gửi Bình Luận</button>
                            </div>
                        ` : `
                            <p><a href="<?php echo BASE_URL; ?>/login.php">Đăng nhập</a> để bình luận</p>
                        `}
                        <div class="comments-list" id="commentsList">
                            <p>Đang tải bình luận...</p>
                        </div>
                    </div>
                `;

                document.getElementById('postContent').innerHTML = html;

                // Load comments
                loadComments();

                // Vote handlers
                if (authData.loggedIn) {
                    document.getElementById('likeBtn').addEventListener('click', async () => {
                        const res = await fetch('<?php echo BASE_URL; ?>/api/votes.php', {
                            method: 'POST',
                            body: new FormData(new FormData().append('action', 'vote').append('post_id', postId).append('type', 'like'))
                        });
                        location.reload();
                    });

                    document.getElementById('dislikeBtn').addEventListener('click', async () => {
                        const res = await fetch('<?php echo BASE_URL; ?>/api/votes.php', {
                            method: 'POST',
                            body: new FormData(new FormData().append('action', 'vote').append('post_id', postId).append('type', 'dislike'))
                        });
                        location.reload();
                    });
                }

            } catch (error) {
                console.error('Error:', error);
                document.getElementById('postContent').innerHTML = '<p>Lỗi khi tải bài viết</p>';
            }
        }

        async function loadComments() {
            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/comments.php?action=list&post_id=' + postId);
                const data = await res.json();

                let html = '';
                if (data.comments && data.comments.length > 0) {
                    html = data.comments.map(comment => `
                        <div class="comment-item">
                            <div class="comment-author">${comment.author.username}</div>
                            <div class="comment-date">${comment.created_at}</div>
                            <div class="comment-content">${comment.content}</div>
                        </div>
                    `).join('');
                } else {
                    html = '<p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>';
                }

                document.getElementById('commentsList').innerHTML = html;
            } catch (error) {
                console.error('Error loading comments:', error);
            }
        }

        async function postComment() {
            const content = document.getElementById('commentInput').value.trim();
            if (!content) {
                alert('Vui lòng nhập bình luận');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('post_id', postId);
            formData.append('content', content);

            try {
                const res = await fetch('<?php echo BASE_URL; ?>/api/comments.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('commentInput').value = '';
                    loadComments();
                } else {
                    alert(data.error || 'Lỗi khi đăng bình luận');
                }
            } catch (error) {
                alert('Lỗi kết nối');
            }
        }

        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', async (e) => {
            e.preventDefault();
            const res = await fetch('<?php echo BASE_URL; ?>/api/auth.php?action=logout');
            window.location.reload();
        });
    </script>
</body>
</html>
