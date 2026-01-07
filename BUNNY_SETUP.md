# Configuration Bunny.net Stream

## Étapes de configuration

### 1. Créer une Stream Library sur Bunny.net

1. Connectez-vous à https://panel.bunny.net
2. Allez dans **Stream** > **Stream Libraries**
3. Cliquez sur **Add Stream Library**
4. Notez les informations suivantes:
   - **Library ID** (ex: 12345)
   - **CDN Hostname** (ex: vz-abc123.b-cdn.net)

### 2. Mettre à jour le fichier .env

Ajoutez vos identifiants Bunny.net dans `.env`:

```env
BUNNY_API_KEY=votre_api_key_ici
BUNNY_STREAM_LIBRARY_ID=votre_library_id
BUNNY_STREAM_CDN_HOSTNAME=votre_cdn_hostname
```

### 3. Configuration actuelle

✅ API Key déjà configurée
⚠️ Besoin de Library ID et CDN Hostname

## Fonctionnalités implémentées

### Backend Laravel

✅ **BunnyStreamService** - Service complet pour Bunny.net
- Upload de vidéos
- Génération d'URLs de stream
- Récupération de métadonnées
- Statistiques vidéo
- Suppression de vidéos

✅ **CreatorController**
- Upload de vidéos vers Bunny
- Statistiques de revenus
- Demande de statut créateur

✅ **VideoController** 
- Liste des vidéos avec thumbnails Bunny
- Playback avec URLs Bunny Stream
- Logs de visionnage

✅ **SubscriptionController**
- Création d'abonnements
- Vérification du statut

### Routes API disponibles

```
POST   /api/creator/apply          - Devenir créateur
POST   /api/creator/upload          - Upload vidéo vers Bunny
GET    /api/creator/revenue         - Statistiques revenus

GET    /api/videos                  - Liste des vidéos
GET    /api/videos/{id}             - Détail vidéo
GET    /api/videos/{id}/play        - URLs de lecture Bunny
POST   /api/videos/{id}/watch       - Logger le visionnage

POST   /api/subscribe               - S'abonner
GET    /api/subscription/status     - Statut abonnement
```

## Prochaines étapes

1. Créer une Stream Library sur Bunny.net
2. Ajouter Library ID et CDN Hostname au .env
3. Tester l'upload depuis l'application Flutter
4. Implémenter le player vidéo Flutter

## Test avec Postman/Insomnia

### Upload vidéo
```http
POST http://localhost:8000/api/creator/upload
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Ma première vidéo",
  "description": "Test upload Bunny",
  "category": "tech"
}
```

Réponse:
```json
{
  "video": {...},
  "upload_url": "https://video.bunnycdn.com/library/{id}/videos/{guid}",
  "bunny_video_id": "abc-123-def"
}
```

### Lire une vidéo
```http
GET http://localhost:8000/api/videos/1/play
Authorization: Bearer {token}
```

Réponse:
```json
{
  "video_id": "abc-123",
  "stream_url": "https://vz-xxx.b-cdn.net/abc-123/playlist.m3u8",
  "embed_url": "https://iframe.mediadelivery.net/embed/{lib_id}/{video_id}",
  "thumbnail": "https://vz-xxx.b-cdn.net/abc-123/thumbnail.jpg"
}
```
