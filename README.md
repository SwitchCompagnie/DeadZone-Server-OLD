<div align="center">

# 🎮 Recapturing The DeadZone

**A private server for The Last Stand: Dead Zone**

[![Discord](https://img.shields.io/discord/YOUR_DISCORD_ID?color=7289da&logo=discord&logoColor=white)](https://discord.gg/jFyAePxDBJ)
[![Java](https://img.shields.io/badge/Java-25-orange.svg)](https://www.oracle.com/java/technologies/downloads)
[![Kotlin](https://img.shields.io/badge/Kotlin-2.0-purple.svg)](https://kotlinlang.org/)
[![MariaDB](https://img.shields.io/badge/MariaDB-11+-blue.svg)](https://mariadb.org/)

</div>
<div align="center">

[![en](https://img.shields.io/badge/lang-en-red.svg)](./README.md)
[![fr](https://img.shields.io/badge/lang-fr-yellow.svg)](./README.fr.md)
[![pt-br](https://img.shields.io/badge/lang-pt--br-green.svg)](./README.pt-br.md)

</div>


---

## 📖 About

Recapturing The DeadZone is a community revival project for The Last Stand: Dead Zone. This private server allows players to relive the original game experience with a modern infrastructure developed in Kotlin.

> **⚠️ Note:** Note: This is an unofficial project. All rights to The Last Stand: Dead Zone are the exclusive property of Con Artist Games, who has granted us permission for this project.

> 📚 See [ARCHITECTURE.md](./ARCHITECTURE.md) for more details.

## 📋 Prerequisites

- **Java 25** or higher - [Download](https://www.oracle.com/java/technologies/downloads)
- **MariaDB 11+** - [Installation Guide](https://mariadb.org/download/)
- **Gradle** (included via wrapper)

## 🚀 Installation

### 1. Configure the database

```sql
CREATE DATABASE prod_deadzone_game;
```

The tables will be automatically created when the server is started for the first time.

### 2. Server Configuration

Create the file `src/main/resources/application.yaml` from the template:

```bash
cp src/main/resources/application.yaml.example src/main/resources/application.yaml
```

Then modify with your settings:

```yaml
maria:
  url: jdbc:mariadb://localhost:3306/prod_deadzone_game
  user: root
  password: ""

game:
  host: 127.0.0.1
  port: 7777
```

> **Note:** The `application.yaml` file contains sensitive information and should not be committed. It is ignored by `.gitignore`.

### 3. Compile the project

**Windows:**
```bash
build.bat
```

**Linux/macOS:**
```bash
chmod +x build.sh
./build.sh
```

### 4. Start the server

**Production mode (from the `deploy` folder):**
```bash
# Windows
autorun.bat

# Linux/macOS
./autorun.sh
```

**Development mode:**
```bash
./gradlew run
```

The server will be accessible at:
- **HTTP API:** `http://127.0.0.1:8080`
- **Game Socket:** `127.0.0.1:7777`
- **Broadcast:** Ports `2121-2123`
- **Flash Policy:** Port `843`

### 5. (Optional) Open the Game

If needed, you can use the [Deadzone Dev Launcher](https://github.com/victorgrodriguesm7/deadzone-dev-launcher) to access the game locally.

## 🔧 Technical stack

- **Language:** Kotlin 2.0.21
- **Framework:** Ktor (WebSockets + REST API)
- **Database:** MariaDB + Exposed ORM
- **Serialization:** kotlinx.serialization (JSON + ProtoBuf)
- **Build:** Gradle 8.14
- **Architecture:** Clean Architecture + Factory Pattern

## 🤝 Contribute

Contributions are welcome! Here's how to participate:

1. **Fork** the project
2. **Create** a branch for your feature
```bash
   git checkout -b feature/new-feature
   ```
3. **Commit** your changes
```bash
  git commit -m “Adding a new feature”
  ```
4. **Push** to your branch
```bash
  git push origin feature/new-feature
  ```
5. **Open** a Pull Request

### Contribution guidelines

- Follow standard Kotlin conventions
- Add clear comments for important functions
- Test your changes before submitting
- Document new features

## 📚 Documentation

### Project Architecture

For an in-depth understanding of the architecture, see:

📖 **[ARCHITECTURE.md](./ARCHITECTURE.md)** - Complete documentation:
- Detailed project structure
- Client/server communication flow
- Main components and services
- Message types (100+ types)
- Development guide

### External Documentation

Complete documentation available on our dedicated repository:

🔗 [DeadZone Documentation](https://github.com/glennhenry/DeadZone-Documentation)

## 💬 Community

- **Discord:** [Join the server](https://discord.gg/jFyAePxDBJ)
- **Issues:** [Report a bug](https://github.com/SulivanM/Sandbox-DZ/issues)
- **Discussions:** Share your ideas and get help

## 📝 License

This project is a community revival effort. All rights to The Last Stand: Dead Zone belong to Con Artist Games.

---

<div align="center">

Developed with ❤️ by the DeadZone community

[Discord](https://discord.gg/jFyAePxDBJ) • [Website](https://github.com/SwitchCompagnie/Deadzone-Revive-Website-Game)

</div>