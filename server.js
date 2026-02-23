const express = require('express');
const fs = require('fs');
const path = require('path');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const app = express();
const PORT = process.env.PORT || 4173;
const JWT_SECRET = process.env.JWT_SECRET || 'devda-super-secret';

const DATA_DIR = path.join(__dirname, 'data');
const USERS_FILE = path.join(DATA_DIR, 'users.json');
const POSTS_FILE = path.join(DATA_DIR, 'posts.json');
const COMMENTS_FILE = path.join(DATA_DIR, 'comments.json');

function readJson(filePath) {
  return JSON.parse(fs.readFileSync(filePath, 'utf-8'));
}

function writeJson(filePath, data) {
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
}

function nextId(items) {
  return items.length ? Math.max(...items.map((item) => item.id)) + 1 : 1;
}

function requireAuth(req, res, next) {
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token) {
    return res.status(401).json({ message: 'Thiếu token xác thực.' });
  }

  try {
    req.user = jwt.verify(token, JWT_SECRET);
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Token không hợp lệ hoặc hết hạn.' });
  }
}

app.use(express.json());
app.use(express.static(__dirname));

app.get('/api/health', (req, res) => {
  res.json({ ok: true, service: 'devDA-blog-api' });
});

app.post('/api/auth/signup', async (req, res) => {
  const { name, email, password } = req.body;
  if (!name || !email || !password) {
    return res.status(400).json({ message: 'Vui lòng nhập đủ name, email, password.' });
  }

  const users = readJson(USERS_FILE);
  const exists = users.find((user) => user.email.toLowerCase() === email.toLowerCase());
  if (exists) {
    return res.status(409).json({ message: 'Email đã tồn tại.' });
  }

  const passwordHash = await bcrypt.hash(password, 10);
  const user = {
    id: nextId(users),
    name,
    email,
    passwordHash,
    createdAt: new Date().toISOString()
  };

  users.push(user);
  writeJson(USERS_FILE, users);
  return res.status(201).json({ message: 'Đăng ký thành công.' });
});

app.post('/api/auth/signin', async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ message: 'Thiếu email hoặc password.' });
  }

  const users = readJson(USERS_FILE);
  const user = users.find((item) => item.email.toLowerCase() === email.toLowerCase());
  if (!user) {
    return res.status(401).json({ message: 'Sai tài khoản hoặc mật khẩu.' });
  }

  const valid = await bcrypt.compare(password, user.passwordHash);
  if (!valid) {
    return res.status(401).json({ message: 'Sai tài khoản hoặc mật khẩu.' });
  }

  const token = jwt.sign({ id: user.id, name: user.name, email: user.email }, JWT_SECRET, {
    expiresIn: '1d'
  });

  return res.json({ token, user: { id: user.id, name: user.name, email: user.email } });
});

app.get('/api/posts', (req, res) => {
  const posts = readJson(POSTS_FILE);
  const comments = readJson(COMMENTS_FILE);

  const payload = posts
    .map((post) => ({
      ...post,
      commentsCount: comments.filter((comment) => comment.postId === post.id).length
    }))
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  return res.json(payload);
});

app.get('/api/posts/:id', (req, res) => {
  const posts = readJson(POSTS_FILE);
  const post = posts.find((item) => item.id === Number(req.params.id));
  if (!post) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }
  return res.json(post);
});

app.post('/api/posts', requireAuth, (req, res) => {
  const { title, excerpt, content } = req.body;
  if (!title || !excerpt || !content) {
    return res.status(400).json({ message: 'Vui lòng nhập title, excerpt, content.' });
  }

  const posts = readJson(POSTS_FILE);
  const post = {
    id: nextId(posts),
    title,
    excerpt,
    content,
    authorId: req.user.id,
    authorName: req.user.name,
    createdAt: new Date().toISOString()
  };

  posts.push(post);
  writeJson(POSTS_FILE, posts);
  return res.status(201).json(post);
});

app.get('/api/posts/:id/comments', (req, res) => {
  const comments = readJson(COMMENTS_FILE)
    .filter((item) => item.postId === Number(req.params.id))
    .sort((a, b) => new Date(a.createdAt) - new Date(b.createdAt));

  return res.json(comments);
});

app.post('/api/posts/:id/comments', requireAuth, (req, res) => {
  const { content } = req.body;
  const postId = Number(req.params.id);
  if (!content) {
    return res.status(400).json({ message: 'Nội dung comment không được trống.' });
  }

  const posts = readJson(POSTS_FILE);
  const post = posts.find((item) => item.id === postId);
  if (!post) {
    return res.status(404).json({ message: 'Không tìm thấy bài viết.' });
  }

  const comments = readJson(COMMENTS_FILE);
  const comment = {
    id: nextId(comments),
    postId,
    userId: req.user.id,
    authorName: req.user.name,
    content,
    createdAt: new Date().toISOString()
  };

  comments.push(comment);
  writeJson(COMMENTS_FILE, comments);
  return res.status(201).json(comment);
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
