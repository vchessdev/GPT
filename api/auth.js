const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { readJson, writeJson, nextId } = require('./utils/store');
const { JWT_SECRET } = require('./middleware/auth');

const router = express.Router();

router.post('/signup', async (req, res) => {
  const { name, email, password } = req.body;
  if (!name || !email || !password) {
    return res.status(400).json({ message: 'Vui lòng nhập đủ name, email, password.' });
  }

  const users = readJson('users');
  const exists = users.find((user) => user.email.toLowerCase() === email.toLowerCase());
  if (exists) {
    return res.status(409).json({ message: 'Email đã tồn tại.' });
  }

  const passwordHash = await bcrypt.hash(password, 10);
  users.push({
    id: nextId(users),
    name,
    email,
    passwordHash,
    createdAt: new Date().toISOString()
  });

  writeJson('users', users);
  return res.status(201).json({ message: 'Đăng ký thành công.' });
});

router.post('/signin', async (req, res) => {
  const { email, password } = req.body;
  if (!email || !password) {
    return res.status(400).json({ message: 'Thiếu email hoặc password.' });
  }

  const users = readJson('users');
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

module.exports = router;
