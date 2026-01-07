# 🎬 Configuration Bunny.net - URGENT

## ⚠️ PROBLÈME ACTUEL

Votre fichier `.env` a :
```env
BUNNY_API_KEY=0c5d804c-16f0-4f4e-9e7a-064a716314a9426fa294-f257-4273-b9da-571b6b9367fe ✅
BUNNY_STREAM_LIBRARY_ID=                                                             ❌ VIDE
BUNNY_STREAM_CDN_HOSTNAME=                                                           ❌ VIDE
```

**Les vidéos ne peuvent PAS être uploadées sans ces informations !**

---

## 🔧 ÉTAPES POUR CONFIGURER

### 1. Connectez-vous à Bunny.net

🔗 **URL**: https://panel.bunny.net

### 2. Créez une Stream Library

1. Dans le menu gauche, cliquez sur **Stream**
2. Cliquez sur **Stream Libraries**
3. Cliquez sur le bouton **Add Stream Library**
4. Donnez un nom (ex: "StreamPlatform" ou "MyVideos")
5. Choisissez la région la plus proche de vos utilisateurs
6. Cliquez sur **Add Library**

### 3. Récupérez les informations

Une fois la library créée, vous verrez :

```
📋 Library Details
├── Library ID: 12345                    ← COPIEZ CECI
├── API Key: [votre clé]                 ← Déjà dans .env ✅
├── CDN Hostname: vz-abc123.b-cdn.net   ← COPIEZ CECI
└── Stream URL: https://vz-abc123.b-cdn.net/
```

### 4. Mettez à jour le fichier .env

Ouvrez `BackendLaravel/.env` et modifiez :

```env
BUNNY_STREAM_LIBRARY_ID=12345                    ← Remplacez par votre Library ID
BUNNY_STREAM_CDN_HOSTNAME=vz-abc123.b-cdn.net   ← Remplacez par votre CDN Hostname
```

### 5. Redémarrez le serveur Laravel

```bash
php artisan config:clear
php artisan cache:clear
php artisan serve
```

---

## 📍 OÙ VONT LES VIDÉOS ?

### Après configuration correcte :

```
┌─────────────────────────────────────────────────┐
│  1. Admin upload vidéo via /admin/videos       │
│     └─> Fichier MP4 depuis votre ordinateur    │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  2. Laravel crée la vidéo sur Bunny.net         │
│     └─> POST à l'API Bunny                      │
│     └─> Reçoit un bunny_video_id (GUID)        │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  3. Laravel upload le fichier vers Bunny        │
│     └─> PUT fichier vers Bunny.net CDN         │
│     └─> Fichier stocké sur serveurs Bunny      │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  4. Bunny.net encode automatiquement            │
│     ├─> Crée plusieurs résolutions (480p,       │
│     │   720p, 1080p, etc.)                      │
│     ├─> Génère les thumbnails                   │
│     └─> Distribue sur CDN mondial              │
└─────────────────┬───────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────┐
│  5. Vidéo accessible partout dans le monde      │
│     ├─> URL HLS: https://vz-xxx.b-cdn.net/...  │
│     ├─> iFrame embed disponible                 │
│     └─> Lecture rapide via CDN                  │
└─────────────────────────────────────────────────┘
```

### Stockage physique :

| Élément | Emplacement | Accès |
|---------|-------------|-------|
| **Fichiers vidéo** | Serveurs Bunny.net CDN (cloud) | Via URL HLS |
| **Thumbnails** | `storage/app/public/thumbnails/` | `/storage/thumbnails/xxx.jpg` |
| **Métadonnées** | MySQL base `stream` table `videos` | Via API Laravel |

---

## 🧪 TESTER APRÈS CONFIGURATION

### 1. Vérifier la connexion Bunny

```bash
php artisan tinker
```

Puis dans tinker :
```php
$bunny = app(\App\Services\BunnyStreamService::class);
$video = $bunny->createVideo('Test Video');
dd($video); // Devrait retourner un array avec 'guid'
```

### 2. Uploader une vidéo test

1. Allez sur `http://localhost:8000/admin`
2. Connectez-vous : `admin@stream.com` / `admin123`
3. Cliquez sur **Vidéos** → **Ajouter une vidéo**
4. Remplissez le formulaire avec une petite vidéo MP4
5. Soumettez

### 3. Vérifier sur Bunny.net

1. Retournez sur https://panel.bunny.net
2. **Stream** → **Video Library** → Votre library
3. Vous devriez voir votre vidéo en cours d'encodage

---

## 📊 URLs GÉNÉRÉES

Une fois une vidéo uploadée, vous aurez :

```php
// Dans la base de données
bunny_video_id: "abc-123-def-456"

// URLs générées automatiquement :
stream_url: "https://vz-xxx.b-cdn.net/abc-123-def-456/playlist.m3u8"
embed_url: "https://iframe.mediadelivery.net/embed/12345/abc-123-def-456"
thumbnail: "https://vz-xxx.b-cdn.net/abc-123-def-456/thumbnail.jpg"
```

Ces URLs seront retournées par l'API `/api/videos/{id}/play` pour l'app Flutter.

---

## 🔍 VÉRIFIER SI CONFIGURÉ

Exécutez cette commande pour voir votre config :

```bash
php artisan tinker
```

```php
echo "Library ID: " . config('bunny.stream.library_id') . "\n";
echo "CDN Hostname: " . config('bunny.stream.cdn_hostname') . "\n";
echo "API Key: " . (config('bunny.api_key') ? 'Configuré ✅' : 'Manquant ❌') . "\n";
```

**Résultat attendu :**
```
Library ID: 12345 ✅
CDN Hostname: vz-abc123.b-cdn.net ✅
API Key: Configuré ✅
```

---

## ❓ FAQ

**Q: Les vidéos sont-elles sur mon serveur ?**
R: Non ! Elles sont sur Bunny.net CDN (cloud mondial). Votre serveur Laravel ne fait que gérer les métadonnées.

**Q: Combien ça coûte ?**
R: ~$1/TB de bande passante. Pour 7TB = ~$7/mois. Avec 20k utilisateurs = 40-90TB/mois = $40-90/mois.

**Q: Puis-je télécharger les vidéos depuis Bunny ?**
R: Oui, via l'API Bunny ou le panel web.

**Q: Que se passe-t-il si je supprime une vidéo dans Laravel ?**
R: Elle est aussi supprimée de Bunny.net automatiquement (voir `VideoController::destroy()`).

**Q: Les miniatures sont où ?**
R: Sur votre serveur dans `storage/app/public/thumbnails/`. Accessible via `/storage/thumbnails/`.

---

## 🚨 ACTIONS IMMÉDIATES

1. ✅ Créer Stream Library sur Bunny.net
2. ✅ Copier Library ID et CDN Hostname
3. ✅ Mettre à jour `.env`
4. ✅ Redémarrer serveur Laravel
5. ✅ Tester upload d'une vidéo

**Ensuite vous pourrez commencer à uploader des vidéos !**
