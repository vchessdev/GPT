const api = {
  async request(url, options = {}) {
    const token = localStorage.getItem('token');
    const headers = {
      'Content-Type': 'application/json',
      ...(options.headers || {})
    };

    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(url, { ...options, headers });
    const contentType = response.headers.get('content-type') || '';
    const rawBody = await response.text();

    let data;
    if (contentType.includes('application/json')) {
      data = rawBody ? JSON.parse(rawBody) : {};
    } else {
      data = { message: rawBody || 'Phản hồi không phải JSON từ server.' };
    }

    if (!response.ok) {
      throw new Error(data.message || 'Có lỗi xảy ra');
    }

    return data;
  },
  signup(payload) {
    return this.request('/api/auth/signup', { method: 'POST', body: JSON.stringify(payload) });
  },
  signin(payload) {
    return this.request('/api/auth/signin', { method: 'POST', body: JSON.stringify(payload) });
  },
  checkSession() {
    return this.request('/api/check_session');
  },
  getPosts() {
    return this.request('/api/get_posts');
  },
  getPost(postId) {
    return this.request(`/api/get_posts/${postId}`);
  },
  getComments(postId) {
    return this.request(`/api/get_comments/${postId}`);
  },
  createPost(payload) {
    return this.request('/api/add_posts', { method: 'POST', body: JSON.stringify(payload) });
  },
  createComment(postId, payload) {
    return this.request(`/api/add_comment/${postId}`, { method: 'POST', body: JSON.stringify(payload) });
  },
  deletePost(postId) {
    return this.request(`/api/delete_posts/${postId}`, { method: 'DELETE' });
  },
  deleteComment(commentId) {
    return this.request(`/api/delete_comment/${commentId}`, { method: 'DELETE' });
  },
  vote(postId) {
    return this.request(`/api/votes/${postId}`, { method: 'POST' });
  }
};

const postsRoot = document.getElementById('postsRoot');
const messageEl = document.getElementById('message');
const userStatus = document.getElementById('userStatus');
const authModal = document.getElementById('authModal');
const createModal = document.getElementById('createModal');
const postModal = document.getElementById('postModal');
const postDetail = document.getElementById('postDetail');

const openAuthBtn = document.getElementById('openAuthBtn');
const openCreateBtn = document.getElementById('openCreateBtn');
const logoutBtn = document.getElementById('logoutBtn');

const signupForm = document.getElementById('signupForm');
const signinForm = document.getElementById('signinForm');
const postForm = document.getElementById('postForm');

function setMessage(message, isError = false) {
  messageEl.style.color = isError ? '#ffb5b5' : '#9fffb8';
  messageEl.textContent = message;
}

function getUser() {
  return JSON.parse(localStorage.getItem('user') || 'null');
}

async function updateUserStatus() {
  const user = getUser();
  const topActions = document.querySelector('.top-actions');
  
  if (!user) {
    userStatus.textContent = 'Bạn chưa đăng nhập.';
    openAuthBtn.style.display = 'inline-block';
    openCreateBtn.style.display = 'none';
    logoutBtn.style.display = 'none';
    return;
  }

  try {
    const session = await api.checkSession();
    userStatus.textContent = `Xin chào, ${session.user.name}. Bạn có thể đăng bài và bình luận.`;
    
    // Update top bar with user avatar and name
    openAuthBtn.innerHTML = `<img src="${getAvatarUrl(user.email)}" alt="avatar" class="topbar-avatar" /> ${escapeHtml(user.name)}`;
    openAuthBtn.style.display = 'none';
    openCreateBtn.style.display = 'inline-block';
    logoutBtn.style.display = 'inline-block';
  } catch (error) {
    userStatus.textContent = 'Phiên đăng nhập đã hết hạn.';
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    openAuthBtn.style.display = 'inline-block';
    openCreateBtn.style.display = 'none';
    logoutBtn.style.display = 'none';
  }
}

function openModal(modal) {
  modal.classList.remove('hidden');
}

function closeModal(modal) {
  modal.classList.add('hidden');
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function getAvatarUrl(email) {
  return `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(email)}`;
}

async function renderPosts() {
  postsRoot.innerHTML = 'Đang tải bài viết...';
  const posts = await api.getPosts();

  if (!posts.length) {
    postsRoot.innerHTML = '<p>Chưa có bài viết.</p>';
    return;
  }

  postsRoot.innerHTML = posts
    .map(
      (post) => `
      <article class="post-card" data-post-id="${post.id}">
        <div class="post-header">
          <img src="${getAvatarUrl(post.authorEmail || post.authorName)}" alt="avatar" class="post-avatar" />
          <div class="post-author-info">
            <strong>${escapeHtml(post.authorName)}</strong>
            <span class="time">${new Date(post.createdAt).toLocaleString('vi-VN')}</span>
          </div>
        </div>
        <h3>${escapeHtml(post.title)}</h3>
        <p>${escapeHtml(post.excerpt)}</p>
        <p class="meta">${post.commentsCount || 0} bình luận · ${post.votes || 0} votes</p>
      </article>
    `
    )
    .join('');

  document.querySelectorAll('.post-card').forEach((card) => {
    card.addEventListener('click', async () => {
      const postId = Number(card.dataset.postId);
      await openPostPopup(postId);
    });
  });
}

async function openPostPopup(postId) {
  try {
    const [post, comments] = await Promise.all([api.getPost(postId), api.getComments(postId)]);
    const currentUser = getUser();
    const isAuthor = currentUser && currentUser.id === post.authorId;

    postDetail.innerHTML = `
      <div class="post-detail-header">
        <div class="post-author-block">
          <img src="${getAvatarUrl(post.authorEmail || post.authorName)}" alt="avatar" class="post-detail-avatar" />
          <div>
            <strong>${escapeHtml(post.authorName)}</strong>
            <div class="time">${new Date(post.createdAt).toLocaleString('vi-VN')}</div>
          </div>
          ${isAuthor ? `<button id="deletePostBtn" class="ghost btn-sm">Xóa bài</button>` : ''}
        </div>
      </div>
      <h2>${escapeHtml(post.title)}</h2>
      <p>${escapeHtml(post.content)}</p>
      <div class="post-actions">
        <button id="voteBtn" class="ghost">👍 Vote (${post.votes || 0})</button>
      </div>
      <h3>Bình luận (${comments.length})</h3>
      <div class="comments-section">
        ${comments.length
          ? comments.map((comment) => `
            <div class="comment-item">
              <div class="comment-header">
                <img src="${getAvatarUrl(comment.authorEmail || comment.authorName)}" alt="avatar" class="comment-avatar" />
                <div class="comment-meta">
                  <strong>${escapeHtml(comment.authorName)}</strong>
                  <span class="time">${new Date(comment.createdAt).toLocaleString('vi-VN')}</span>
                </div>
              </div>
              <p>${escapeHtml(comment.content)}</p>
            </div>
          `).join('')
          : '<p class="muted">Chưa có bình luận.</p>'}
      </div>
      ${currentUser ? `
        <form id="commentForm" class="form">
          <input name="content" placeholder="Viết bình luận..." required />
          <button type="submit">Gửi bình luận</button>
        </form>
      ` : '<p class="muted">Đăng nhập để bình luận.</p>'}
    `;

    if (isAuthor) {
      document.getElementById('deletePostBtn').addEventListener('click', async () => {
        if (confirm('Bạn chắc chắn muốn xóa bài viết này?')) {
          try {
            await api.deletePost(postId);
            setMessage('Đã xóa bài viết.');
            closeModal(postModal);
            await renderPosts();
          } catch (error) {
            setMessage(error.message, true);
          }
        }
      });
    }

    document.getElementById('voteBtn').addEventListener('click', async () => {
      if (!currentUser) {
        setMessage('Đăng nhập để vote bài viết.', true);
        return;
      }
      try {
        await api.vote(postId);
        setMessage('Vote thành công.');
        await openPostPopup(postId);
        await renderPosts();
      } catch (error) {
        setMessage(error.message, true);
      }
    });

    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
      commentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
          const content = new FormData(event.target).get('content');
          await api.createComment(postId, { content });
          setMessage('Bình luận thành công.');
          await openPostPopup(postId);
          await renderPosts();
        } catch (error) {
          setMessage(error.message, true);
        }
      });
    }

    openModal(postModal);
  } catch (error) {
    setMessage(error.message, true);
  }
}

openAuthBtn.addEventListener('click', () => openModal(authModal));
openCreateBtn.addEventListener('click', () => {
  if (!getUser()) {
    setMessage('Đăng nhập để đăng bài.', true);
    openModal(authModal);
  } else {
    openModal(createModal);
  }
});
logoutBtn.addEventListener('click', () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  updateUserStatus();
  setMessage('Đã đăng xuất.');
  renderPosts();
});

document.querySelectorAll('[data-close]').forEach((button) => {
  button.addEventListener('click', () => {
    const targetId = button.getAttribute('data-close');
    closeModal(document.getElementById(targetId));
  });
});

signupForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const payload = Object.fromEntries(new FormData(signupForm).entries());
    await api.signup(payload);
    signupForm.reset();
    setMessage('Đăng ký thành công. Bạn có thể đăng nhập.');
  } catch (error) {
    setMessage(error.message, true);
  }
});

signinForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const payload = Object.fromEntries(new FormData(signinForm).entries());
    const data = await api.signin(payload);
    localStorage.setItem('token', data.token);
    // Store user with email
    localStorage.setItem('user', JSON.stringify({ ...data.user, email: payload.email }));
    signinForm.reset();
    await updateUserStatus();
    closeModal(authModal);
    setMessage('Đăng nhập thành công.');
    await renderPosts();
  } catch (error) {
    setMessage(error.message, true);
  }
});

postForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const payload = Object.fromEntries(new FormData(postForm).entries());
    await api.createPost(payload);
    postForm.reset();
    closeModal(createModal);
    setMessage('Đăng bài thành công.');
    await renderPosts();
  } catch (error) {
    setMessage(error.message, true);
  }
});

updateUserStatus();
renderPosts().catch((error) => setMessage(error.message, true));
