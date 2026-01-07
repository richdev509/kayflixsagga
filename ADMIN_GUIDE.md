# Guide d'administration - StreamPlatform

## 🔐 Accès à l'administration

### URL d'accès
```
http://localhost:8000/admin
```

### Identifiants de connexion

**Administrateur:**
- Email: `admin@stream.com`
- Mot de passe: `admin123`

**Créateur de test:**
- Email: `creator@stream.com`
- Mot de passe: `creator123`

---

## 📹 Gestion des vidéos

### 1. Uploader une vidéo

1. **Accéder à l'upload**
   - Allez dans `Admin > Vidéos`
   - Cliquez sur "Ajouter une vidéo"

2. **Remplir le formulaire**
   - Sélectionner un créateur
   - Titre de la vidéo
   - Description détaillée
   - Catégorie
   - Fichier vidéo (MP4, MOV, AVI, WMV - Max 2GB)
   - Miniature (optionnel - Bunny générera automatiquement si non fournie)

3. **Processus d'upload**
   - La vidéo est uploadée vers Bunny.net
   - Bunny.net encode automatiquement la vidéo
   - Le statut passe de "Brouillon" à "Publié" une fois l'encodage terminé

### 2. Gérer les vidéos existantes

**Actions disponibles:**
- 👁️ **Voir**: Détails complets + statistiques Bunny.net
- ✏️ **Modifier**: Titre, description, catégorie, miniature
- 🔄 **Actualiser**: Synchroniser avec Bunny.net
- 👁️/👁️‍🗨️ **Publier/Dépublier**: Contrôler la visibilité
- 🗑️ **Supprimer**: Supprime de la DB et de Bunny.net

### 3. Statuts Bunny.net

| Statut | Signification |
|--------|---------------|
| En attente | Vidéo créée, en attente d'upload |
| Processing | Upload en cours |
| Encoding | Encodage en cours |
| Finished | Encodage terminé |
| Resolution Finished | Toutes les résolutions générées ✅ |
| Error | Erreur lors du traitement ❌ |

---

## 👥 Gestion des créateurs

### Approuver un créateur

1. Allez dans `Admin > Créateurs`
2. Trouvez le créateur "En attente"
3. Cliquez sur ✅ **Approuver** ou ❌ **Rejeter**
4. Une fois approuvé, le créateur peut uploader des vidéos

### Statuts des créateurs

- **En attente** (pending): Demande soumise
- **Approuvé** (approved): Peut uploader des vidéos
- **Rejeté** (rejected): Demande refusée

---

## 📊 Dashboard

Le dashboard affiche:

### Statistiques principales
- **Utilisateurs totaux**
- **Vidéos totales** / Vidéos publiées
- **Créateurs actifs**
- **Revenus mensuels** (basé sur les abonnements actifs)
- **Abonnements actifs**
- **Créateurs en attente d'approbation**

### Vidéos récentes
Liste des 10 dernières vidéos uploadées avec aperçu et actions rapides

### Créateurs en attente
Liste des demandes de créateurs à approuver/rejeter

---

## ⚙️ Configuration Bunny.net

### Informations requises

Avant d'uploader des vidéos, configurez dans `.env`:

```env
BUNNY_API_KEY=0c5d804c-16f0-4f4e-9e7a-064a716314a9426fa294-f257-4273-b9da-571b6b9367fe
BUNNY_STREAM_LIBRARY_ID=votre_library_id
BUNNY_STREAM_CDN_HOSTNAME=vz-xxx.b-cdn.net
```

### Obtenir Library ID et CDN Hostname

1. Connectez-vous sur https://panel.bunny.net
2. Allez dans **Stream** > **Stream Libraries**
3. Cliquez sur **Add Stream Library** (si pas encore créé)
4. Notez:
   - **Library ID** (ex: 12345)
   - **CDN Hostname** (ex: vz-abc123.b-cdn.net)
5. Ajoutez ces valeurs dans `.env`

---

## 🎬 Workflow d'upload complet

```
1. Admin se connecte
   ↓
2. Crée/Approuve un créateur
   ↓
3. Admin > Vidéos > Ajouter
   ↓
4. Remplit le formulaire + Upload fichier
   ↓
5. Vidéo créée dans Bunny.net
   ↓
6. Upload du fichier vers Bunny
   ↓
7. Bunny encode la vidéo (quelques minutes)
   ↓
8. Admin clique "Actualiser" pour vérifier le statut
   ↓
9. Quand statut = "Resolution Finished", cliquer "Publier"
   ↓
10. Vidéo disponible dans l'API pour l'app Flutter
```

---

## 📱 Intégration avec l'application Flutter

Une fois les vidéos uploadées et publiées:

### API Endpoints disponibles

```
GET /api/videos
- Liste toutes les vidéos publiées
- Inclut: thumbnail_url, titre, créateur, catégorie

GET /api/videos/{id}/play
- Retourne les URLs de streaming Bunny
- stream_url: Playlist HLS
- embed_url: iFrame player
- thumbnail: Image miniature
```

### Exemple de réponse API

```json
{
  "video_id": "abc-123-def",
  "stream_url": "https://vz-xxx.b-cdn.net/abc-123/playlist.m3u8",
  "embed_url": "https://iframe.mediadelivery.net/embed/12345/abc-123",
  "thumbnail": "https://vz-xxx.b-cdn.net/abc-123/thumbnail.jpg"
}
```

---

## 🔧 Commandes utiles

### Créer un admin
```bash
php artisan db:seed --class=AdminUserSeeder
```

### Créer un créateur
```bash
php artisan db:seed --class=CreatorUserSeeder
```

### Vider le cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Lister les routes
```bash
php artisan route:list
```

### Lancer le serveur
```bash
php artisan serve
```

---

## 📝 Notes importantes

1. **Taille maximum des vidéos**: 2GB (configurable dans `VideoController`)
2. **Formats acceptés**: MP4, MOV, AVI, WMV
3. **Miniatures**: Générées automatiquement par Bunny si non fournies
4. **Encodage**: Prend quelques minutes selon la taille de la vidéo
5. **Stockage**: Vidéos stockées sur Bunny.net CDN (pas sur votre serveur)
6. **Streaming**: HLS adaptatif avec plusieurs résolutions automatiques

---

## 🆘 Dépannage

### La vidéo ne s'upload pas
- Vérifier la taille (< 2GB)
- Vérifier le format (MP4 recommandé)
- Vérifier la connexion Bunny.net (API Key valide)

### Statut "Error" sur Bunny
- Cliquer "Actualiser" pour récupérer les détails
- Vérifier les logs Bunny.net
- Essayer de re-uploader

### Page blanche sur /admin
- Vérifier que vous êtes connecté
- Vérifier que l'utilisateur a le rôle "admin"
- Vérifier les logs Laravel: `storage/logs/laravel.log`

---

## 📧 Support

Pour toute question:
- Consultez `BUNNY_SETUP.md` pour la config Bunny.net
- Vérifiez les logs: `storage/logs/laravel.log`
- Testez les routes API avec Postman/Insomnia
