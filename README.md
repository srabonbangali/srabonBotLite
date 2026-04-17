```markdown
# 🤖 SrabonBot Lite - Free Telegram Group Management Bot

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![Telegram](https://img.shields.io/badge/Telegram-Bot-0088cc.svg)](https://core.telegram.org/bots)
[![GitHub stars](https://img.shields.io/github/stars/srabonhasan/srabonbot-lite.svg)](https://github.com/srabonhasan/srabonbot-lite/stargazers)

A **lightweight, free, and open-source** Telegram group management bot with essential moderation features. Perfect for small to medium-sized groups that need reliable moderation without complexity.

## ✨ Features

### 🔧 Moderation Tools
- ✅ **Mute/Unmute** users with duration support (10m, 2h, 1d, or permanent)
- ✅ **Kick/Ban/Unban** troublesome members instantly
- ✅ **Warning System** - Automatic 3-strike policy (3 warnings = auto-kick)
- ✅ **Message Deletion** - Remove inappropriate messages with /del

### 🛡️ Auto-Moderation
- ✅ **Word Filter** - Auto-delete messages containing forbidden words
- ✅ **Chat Lock** - Restrict entire chat to administrators only
- ✅ **Welcome Messages** - Customizable greeting for new members
- ✅ **Goodbye Messages** - Customizable farewell for leaving members

### 👑 Admin Management
- ✅ **Promote/Demote** administrators with proper permissions
- ✅ **Pin/Unpin** important messages to keep them visible
- ✅ **Admin List** - View all group administrators at once
- ✅ **User Info** - Get detailed information about any member
- ✅ **Group Info** - View complete group statistics

### 📊 Statistics & Data
- ✅ **Warning counts** tracking per user per group
- ✅ **Active filters** list with management tools
- ✅ **Bot statistics** overview (groups, filters, warnings)

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- HTTPS web server (required for Telegram webhook)
- Telegram Bot Token (get from [@BotFather](https://t.me/BotFather))

### Installation in 5 Minutes

#### Step 1: Get Bot Token
1. Open Telegram and search for [@BotFather](https://t.me/BotFather)
2. Send `/newbot` and follow instructions
3. Copy your bot token (looks like: `123456789:ABCdefGHIjklmNOPqrstUVwxyz`)

#### Step 2: Get Your User ID
1. Message [@userinfobot](https://t.me/userinfobot) on Telegram
2. It will reply with your user ID (e.g., `123456789`)
3. This will be your `SUPER_ADMIN_ID`

#### Step 3: Download & Configure
```bash
# Clone the repository
git clone https://github.com/srabonhasan/srabonbot-lite.git
cd srabonbot-lite

# Edit configuration
nano bot.php
```

Change these values in `bot.php`:
```php
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE');  // Paste your bot token
define('SUPER_ADMIN_ID', 123456789);         // Paste your user ID
```

#### Step 4: Upload to Server
```bash
# Upload bot.php to your web server (e.g., /var/www/html/bot.php)
# Using FTP or SCP
```

#### Step 5: Set Webhook
```bash
# Replace YOUR_BOT_TOKEN and yourdomain.com
curl -F "url=https://yourdomain.com/bot.php" "https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook"
```

#### Step 6: Set Permissions
```bash
# Make sure data files are writable
chmod 666 warnings.json welcome.json filters.json locked.json settings.json
```

### Verify Installation
1. Open Telegram and message your bot
2. Send `/start` - Should see welcome message
3. Send `/help` - Should see command list
4. Add bot to a group as admin

## 📝 Complete Command Reference

### 👑 Admin Commands (Group admins only)

| Command | Description | Example |
|---------|-------------|---------|
| `/mute [time]` | Mute a user (reply to message) | `/mute 10m` or `/mute 2h` |
| `/unmute` | Unmute a user (reply) | `/unmute` |
| `/kick` | Remove user from group (reply) | `/kick` |
| `/ban` | Permanently ban user (reply) | `/ban` |
| `/unban` | Unban user (reply to any their message) | `/unban` |
| `/warn` | Give warning (3 = auto-kick) | `/warn` |
| `/unwarn` | Remove a warning | `/unwarn` |
| `/warnings` | Check user's warning count | `/warnings` |
| `/promote` | Make user admin | `/promote` |
| `/demote` | Remove admin rights | `/demote` |
| `/pin` | Pin replied message | `/pin` |
| `/unpin` | Unpin all messages | `/unpin` |
| `/del` | Delete replied message | `/del` |
| `/adminlist` | List all group admins | `/adminlist` |
| `/info` | Get user info (reply or username) | `/info` or reply |
| `/groupinfo` | Get group statistics | `/groupinfo` |
| `/setwelcome [msg]` | Set custom welcome message | `/setwelcome Welcome {name}!` |
| `/setgoodbye [msg]` | Set custom goodbye message | `/setgoodbye Bye {name}` |
| `/filter [word]` | Add word to auto-delete filter | `/filter spam` |
| `/unfilter [word]` | Remove word from filter | `/unfilter spam` |
| `/filters` | List all active filters | `/filters` |
| `/lock` | Lock chat (admins only) | `/lock` |
| `/unlock` | Unlock chat for everyone | `/unlock` |
| `/resetwarns` | Reset all warnings in group | `/resetwarns` |

### 🌐 Public Commands (All users)

| Command | Description |
|---------|-------------|
| `/start` | Show bot information |
| `/help` | Display all commands |
| `/stats` | Show bot statistics |

## 🔧 Configuration Guide

### Welcome/Goodbye Message Variables
- `{name}` - User's full name (first + last)
- `{username}` - User's Telegram username with @

**Example:**
```
/setwelcome 🎉 Welcome {name} ({username}) to our awesome group!
```

### Mute Duration Formats
| Format | Duration |
|--------|----------|
| `10m` | 10 minutes |
| `30m` | 30 minutes |
| `2h` | 2 hours |
| `1d` | 1 day |
| (no time) | Permanent mute |

### Warning System Rules
- Each user starts with 0 warnings
- `/warn` increments warning count by 1
- At 3 warnings, user is automatically kicked
- Warnings are per-group (not global)
- `/resetwarns` clears all warnings in the group

### Filter System
- Add words to auto-delete list with `/filter`
- Messages containing filtered words are instantly deleted
- Super Admin messages bypass filters
- Case-insensitive matching

## 📁 File Structure

```
srabonbot-lite/
├── bot.php              # Main bot script (edit this)
├── warnings.json        # Warning data storage (auto-created)
├── welcome.json         # Welcome/goodbye messages (auto-created)
├── filters.json         # Word filters storage (auto-created)
├── locked.json          # Locked chats storage (auto-created)
├── settings.json        # General settings (auto-created)
├── README.md            # This file
├── LICENSE              # MIT License
└── .gitignore           # Git ignore file
```

## 🔒 Security Features

- ✅ All admin commands verify user permissions via Telegram API
- ✅ Super Admin has full control across all groups (hardcoded ID)
- ✅ No database required - secure flat-file storage
- ✅ Bot token never exposed to users
- ✅ Input validation on all commands

## 🆘 Troubleshooting Guide

### Webhook Not Working?
```bash
# Check webhook status
https://api.telegram.org/botYOUR_TOKEN/getWebhookInfo

# Delete existing webhook
https://api.telegram.org/botYOUR_TOKEN/deleteWebhook

# Set new webhook
https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://yourdomain.com/bot.php
```

### Bot Not Responding?
1. Check PHP error logs: `tail -f /var/log/php_errors.log`
2. Verify file permissions: `chmod 666 *.json`
3. Ensure bot is admin in the group
4. Check webhook URL is accessible (HTTPS required)

### Common Errors

**Error: "Bad Request: chat not found"**
- Bot needs to be added to the group first
- Make sure bot is admin in the group

**Error: "Message can't be deleted"**
- Bot needs delete_message permission
- Messages older than 48 hours can't be deleted

**Files not saving?**
```bash
chmod 777 .  # Give write permissions to directory
```

## 💎 Upgrade to Premium

Need more advanced features? Get the **Premium Version** with full support and hosting!

### 🚀 Premium Features:
- ✅ **Message History** - Full SQLite database with all messages
- ✅ **Search Messages** - Find any message with `/searchmsg`
- ✅ **Anti-Spam System** - Automatic spam detection and blocking
- ✅ **Advanced Analytics** - User activity, top users, message trends
- ✅ **Auto-Delete** - Auto-remove messages after X seconds
- ✅ **Broadcast** - Send messages to all groups at once
- ✅ **Custom Name** - Your bot name, your brand, your logo
- ✅ **24/7 Hosting** - We host it on high-availability servers
- ✅ **Priority Support** - Fast response within 2 hours
- ✅ **Custom Features** - We build exactly what you need
- ✅ **Automatic Backups** - Daily database backups
- ✅ **API Access** - Integrate with your existing systems

### 📦 Premium Packages:

| Feature | Basic | Professional | Enterprise |
|---------|-------|--------------|------------|
| Price | $49/month | $99/month | $299/month |
| Custom Bot Name | ✅ | ✅ | ✅ |
| Message History | ✅ | ✅ | ✅ |
| Search Messages | ✅ | ✅ | ✅ |
| Anti-Spam System | ✅ | ✅ | ✅ |
| Custom Commands | 5 | Unlimited | Unlimited |
| Analytics Dashboard | ❌ | ✅ | ✅ |
| API Access | ❌ | ✅ | ✅ |
| Priority Support | Email (48h) | 24h Response | 2h Response |
| Dedicated Server | ❌ | ❌ | ✅ |
| Custom Development | ❌ | ❌ | ✅ |
| Phone Support | ❌ | ❌ | ✅ |

### 🎯 Why Choose Premium?
- **Fully Managed** - We handle everything: hosting, updates, security
- **Scalable** - From 100 to 100,000+ members
- **Reliable** - 99.9% uptime guarantee
- **Fast** - Optimized servers for instant responses

### 👉 **[Get Premium Bot →](https://srabon.net/srabonbot)**

Contact us for custom enterprise solutions!

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

**Free for personal and commercial use!**

## 👨‍💻 Author

**Srabon Hasan**
- Telegram: [@srabonhasan](https://t.me/srabonhasan)
- Website: [https://srabon.net](https://srabon.net)
- GitHub: [@srabonhasan](https://github.com/srabonhasan)

## 🙏 Acknowledgments

- Telegram Bot API for the amazing platform
- All contributors and users of this bot
- Open source community

## ⭐ Support the Project

If you find this bot useful:
- ⭐ Star the repository on GitHub
- 📢 Share with others
- 💰 Consider upgrading to Premium
- 🐛 Report bugs and suggest features

## 📞 Contact & Support

- **Free Support**: GitHub Issues
- **Premium Support**: Telegram [@srabonhasan](https://t.me/srabonhasan)
- **Email**: srabon@srabon.net
- **Website**: https://srabon.net

---

## 🎯 Quick Commands Reference Card

```
┌─────────────────────────────────────────────────────────────┐
│                    SRABONBOT LITE                           │
│                  Quick Reference Card                       │
├─────────────────────────────────────────────────────────────┤
│ ADMIN COMMANDS                                              │
├─────────────────────────────────────────────────────────────┤
│ /mute 10m    - Mute for 10 minutes                         │
│ /unmute      - Unmute user                                 │
│ /kick        - Remove user                                 │
│ /ban         - Permanent ban                               │
│ /warn        - Give warning (3 = kick)                     │
│ /promote     - Make admin                                  │
│ /pin         - Pin message                                 │
│ /del         - Delete message                              │
│ /lock        - Lock chat (admins only)                     │
│ /filter word - Auto-delete word                            │
├─────────────────────────────────────────────────────────────┤
│ PUBLIC COMMANDS                                             │
├─────────────────────────────────────────────────────────────┤
│ /start       - Bot info                                    │
│ /help        - Show all commands                           │
│ /stats       - Bot statistics                              │
└─────────────────────────────────────────────────────────────┘
```

---

**Made with ❤️ by Srabon Hasan**

*Last Updated: January 2025*
```
