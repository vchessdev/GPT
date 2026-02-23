const jwt = require('jsonwebtoken');

const JWT_SECRET = process.env.JWT_SECRET || 'devda-super-secret';

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

module.exports = {
  requireAuth,
  JWT_SECRET
};
