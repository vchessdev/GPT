const express = require('express');

const app = express();
const PORT = process.env.PORT || 4173;

app.use(express.json());
app.use(express.static(__dirname));

app.get('/api/health', (req, res) => {
  res.json({ ok: true, service: 'devDA-blog-api' });
});

app.use('/api/auth', require('./api/auth'));
app.use('/api/get_posts', require('./api/get_posts'));
app.use('/api/add_posts', require('./api/add_posts'));
app.use('/api/get_comments', require('./api/get_comments'));
app.use('/api/add_comment', require('./api/add_comment'));
app.use('/api/delete_posts', require('./api/delete_posts'));
app.use('/api/delete_comment', require('./api/delete_comment'));
app.use('/api/check_session', require('./api/check_session'));
app.use('/api/votes', require('./api/votes'));

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
