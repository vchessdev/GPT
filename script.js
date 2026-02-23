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
    const data = await response.json();

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
  getPosts() {
    return this.request('/api/posts');
  },
  getPost(postId) {
    return this.request(`/api/posts/${postId}`);
  },
  getComments(postId) {
    return this.request(`/api/posts/${postId}/comments`);
  },
  createPost(payload) {
    return this.request('/api/posts', { method: 'POST', body: JSON.stringify(payload) });
  },
  createComment(postId, payload) {
    return this.request(`/api/posts/${postId}/comments`, { method: 'POST', body: JSON.stringify(payload) });
  }
};

const state = { posts: [] };

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

function updateUserStatus() {
  const user = getUser();
  userStatus.textContent = user ? `Xin chào, ${user.name}. Bạn có thể đăng bài và bình luận.` : 'Bạn chưa đăng nhập.';
}

function openModal(modal) {
  modal.classList.remove('hidden');
}

function closeModal(modal) {
  modal.classList.add('hidden');
}

async function renderPosts() {
  postsRoot.innerHTML = 'Đang tải bài viết...';
  const posts = await api.getPosts();
  state.posts = posts;

  if (!posts.length) {
    postsRoot.innerHTML = '<p>Chưa có bài viết.</p>';
    return;
  }

  postsRoot.innerHTML = posts
    .map(
      (post) => `
      <article class="post-card" data-post-id="${post.id}">
        <h3>${post.title}</h3>
        <p>${post.excerpt}</p>
        <p class="meta">${post.authorName} · ${new Date(post.createdAt).toLocaleString('vi-VN')} · ${post.commentsCount} bình luận</p>
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

    postDetail.innerHTML = `
      <h2>${post.title}</h2>
      <p class="meta">Tác giả: ${post.authorName} · ${new Date(post.createdAt).toLocaleString('vi-VN')}</p>
      <p>${post.content}</p>
      <h3>Bình luận</h3>
      <div>
        ${comments.length
          ? comments.map((comment) => `<div class="comment-item"><b>${comment.authorName}:</b> ${comment.content}</div>`).join('')
          : '<p class="muted">Chưa có bình luận.</p>'}
      </div>
      <form id="commentForm" class="form">
        <input name="content" placeholder="Viết bình luận..." required />
        <button type="submit">Gửi bình luận</button>
      </form>
    `;

    document.getElementById('commentForm').addEventListener('submit', async (event) => {
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

    openModal(postModal);
  } catch (error) {
    setMessage(error.message, true);
  }
}

openAuthBtn.addEventListener('click', () => openModal(authModal));
openCreateBtn.addEventListener('click', () => openModal(createModal));
logoutBtn.addEventListener('click', () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  updateUserStatus();
  setMessage('Đã đăng xuất.');
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
    localStorage.setItem('user', JSON.stringify(data.user));
    signinForm.reset();
    updateUserStatus();
    closeModal(authModal);
    setMessage('Đăng nhập thành công.');
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
