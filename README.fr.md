<div align="center">

# 🎮 Recapturing The DeadZone

**Un serveur privé pour The Last Stand: Dead Zone**

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

## 📖 À propos

Recapturing The DeadZone est un projet de renaissance communautaire pour **The Last Stand: Dead Zone**. Ce serveur privé permet aux joueurs de revivre l'expérience du jeu original avec une infrastructure moderne développée en Kotlin.

> **⚠️ Note:** Il s’agit d’un projet non officiel. Tous les droits de The Last Stand: Dead Zone sont la propriété exclusive de Con Artist Games, qui nous a accordé leur autorisation pour ce projet.

> 📚 Voir [ARCHITECTURE.md](./ARCHITECTURE.md) pour plus de détails

## 📋 Prérequis

- **Java 25** ou supérieur - [Télécharger](https://www.oracle.com/java/technologies/downloads)
- **MariaDB 11+** - [Guide d'installation](https://mariadb.org/download/)
- **Gradle** (inclus via wrapper)

## 🚀 Installation

### 1. Configurer la base de données

```sql
CREATE DATABASE prod_deadzone_game;
```

Les tables seront automatiquement créées au premier démarrage du serveur.

### 2. Configuration

Créer le fichier `src/main/resources/application.yaml` à partir du template:

```bash
cp src/main/resources/application.yaml.example src/main/resources/application.yaml
```

Puis modifier avec vos paramètres:

```yaml
maria:
  url: jdbc:mariadb://localhost:3306/prod_deadzone_game
  user: root
  password: ""

game:
  host: 127.0.0.1
  port: 7777
```

> **Note:** Le fichier `application.yaml` contient des informations sensibles et ne doit pas être committé. Il est ignoré par `.gitignore`.

### 3. Compiler le projet

**Windows:**
```bash
build.bat
```

**Linux/macOS:**
```bash
chmod +x build.sh
./build.sh
```

### 4. Démarrer le serveur

**Mode production (depuis le dossier `deploy`):**
```bash
# Windows
autorun.bat

# Linux/macOS
./autorun.sh
```

**Mode développement:**
```bash
./gradlew run
```

Le serveur sera accessible sur :
- **API HTTP:** `http://127.0.0.1:8080`
- **Socket Game:** `127.0.0.1:7777`
- **Broadcast:** Ports `2121-2123`
- **Policy Flash:** Port `843`

### 5. (Facultatif) Ouvrez le jeu

Si nécessaire, vous pouvez utiliser le [Deadzone Dev Launcher](https://github.com/victorgrodriguesm7/deadzone-dev-launcher) pour accéder au jeu localement.

## 🔧 Stack technique

- **Langage:** Kotlin 2.0.21
- **Framework:** Ktor (WebSockets + REST API)
- **Base de données:** MariaDB + Exposed ORM
- **Sérialisation:** kotlinx.serialization (JSON + ProtoBuf)
- **Build:** Gradle 8.14
- **Architecture:** Clean Architecture + Factory Pattern

## 🤝 Contribuer

Les contributions sont les bienvenues! Voici comment participer:

1. **Fork** le projet
2. **Créer** une branche pour votre fonctionnalité
   ```bash
   git checkout -b feature/nouvelle-fonctionnalite
   ```
3. **Commit** vos changements
   ```bash
   git commit -m "Ajout d'une nouvelle fonctionnalité"
   ```
4. **Push** vers votre branche
   ```bash
   git push origin feature/nouvelle-fonctionnalite
   ```
5. **Ouvrir** une Pull Request

### Directives de contribution

- Suivre les conventions Kotlin standard
- Ajouter des commentaires clairs pour les fonctions importantes
- Tester vos modifications avant de soumettre
- Documenter les nouvelles fonctionnalités

## 📚 Documentation

### Architecture du projet

Pour une compréhension approfondie de l'architecture, consultez :

📖 **[ARCHITECTURE.md](./ARCHITECTURE.md)** - Documentation complète :
- Structure détaillée du projet
- Flux de communication client/serveur
- Composants principaux et services
- Types de messages (100+ types)
- Guide de développement

### Documentation externe

Documentation complète disponible sur notre repository dédié:

🔗 [DeadZone Documentation](https://github.com/glennhenry/DeadZone-Documentation)

## 💬 Communauté

- **Discord:** [Rejoindre le serveur](https://discord.gg/jFyAePxDBJ)
- **Issues:** [Rapporter un bug](https://github.com/SulivanM/Sandbox-DZ/issues)
- **Discussions:** Partager vos idées et obtenir de l'aide

## 📝 Licence

Ce projet est un effort de renaissance communautaire. Tous les droits sur The Last Stand: Dead Zone appartiennent à Con Artist Games.

---

<div align="center">

Développé avec ❤️ par la communauté DeadZone

[Discord](https://discord.gg/jFyAePxDBJ) • [Website](https://github.com/SwitchCompagnie/Deadzone-Revive-Website-Game)

</div>
