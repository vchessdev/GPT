# devDA Blog Platform

A lightweight blog platform with authentication, posts, comments, and voting functionality.

## Quick Start

### Prerequisites
- Node.js 14+
- npm

### Installation

1. Clone the repository
```bash
git clone <repo-url>
cd GPT
```

2. Install dependencies
```bash
npm install
```

3. Set up environment variables
```bash
cp .env.example .env
```

4. Generate a strong JWT_SECRET (important for production)
```bash
# On Linux/macOS:
openssl rand -base64 32

# Add the generated value to .env:
# JWT_SECRET=<generated-value>
```

5. Start the server
```bash
npm start
```

The server will run at `http://localhost:3000`

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login and get JWT token
- `GET /api/check_session` - Check if current session is valid

### Posts
- `GET /api/get_posts` - Get all posts
- `GET /api/get_posts/:id` - Get single post
- `POST /api/add_posts` - Create new post (requires auth)
- `DELETE /api/delete_posts/:id` - Delete post (requires auth)
- `POST /api/votes/:postId` - Vote on a post (requires auth)

### Comments
- `GET /api/get_comments/:postId` - Get comments for a post
- `POST /api/add_comment/:postId` - Add comment (requires auth)
- `DELETE /api/delete_comment/:id` - Delete comment (requires auth)

## Security

This project includes important security fixes:

1. **XSS Prevention** - User content is HTML-escaped before rendering
2. **Race Condition Prevention** - File writes are queued to prevent concurrent data loss
3. **JWT Security** - JWT_SECRET is required as environment variable (not hardcoded)

See [SECURITY.md](SECURITY.md) for detailed information about security implementation and recommendations.

## Project Structure

```
├── api/
│   ├── auth.js              # Authentication endpoints
│   ├── add_posts.js         # Create post
│   ├── get_posts.js         # Fetch posts
│   ├── add_comment.js       # Add comment
│   ├── delete_posts.js      # Delete post
│   ├── delete_comment.js    # Delete comment
│   ├── votes.js             # Vote on post
│   ├── check_session.js     # Session validation
│   ├── middleware/
│   │   └── auth.js          # JWT authentication middleware
│   └── utils/
│       └── store.js         # File-based data storage
├── data/                    # JSON data files
├── script.js                # Frontend JavaScript
├── styles.css               # Styling
├── index.html               # Main HTML
├── server.js                # Express server
├── .env.example             # Environment variables template
├── .gitignore               # Git ignore rules
└── SECURITY.md              # Security documentation
```

## Data Storage

Currently uses JSON files stored in `/data` directory:
- `users.json` - User accounts
- `posts.json` - Blog posts
- `comments.json` - Comments on posts

**Note**: For production, migrate to a real database (MongoDB, PostgreSQL, SQLite).

## Development

### Testing the API

```bash
# Get health status
curl http://localhost:3000/api/health

# Register user
curl -X POST http://localhost:3000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }'

# Login
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

## License

MIT
