# Configuration pour Upload de Fichiers Vidéo > 2GB

## Prérequis Système
- **Séries**: Jusqu'à 30 minutes et plus
- **Films**: Jusqu'à 1 heure et plus
- **Taille maximale supportée**: Plus de 2GB par fichier

## 1. Configuration PHP (php.ini)

Fichier: `C:\xamp3\php\php.ini`

Modifiez les paramètres suivants:

```ini
; Taille maximale d'un fichier uploadé (3GB)
upload_max_filesize = 3000M

; Taille maximale des données POST (doit être >= upload_max_filesize)
post_max_size = 3000M

; Temps maximum d'exécution d'un script (1 heure)
max_execution_time = 3600

; Temps maximum pour recevoir les données (1 heure)
max_input_time = 3600

; Mémoire maximale allouée à un script
memory_limit = 512M

; Pour les uploads en morceaux (chunked upload)
max_file_uploads = 20
```

**⚠️ Important**: Redémarrez Apache après modification:
```bash
# Dans XAMPP Control Panel
Arrêtez Apache → Démarrez Apache
```

## 2. Configuration Apache (.htaccess)

Fichier: `BackendLaravel/public/.htaccess`

✅ **Déjà configuré automatiquement**

Les directives PHP ont été ajoutées pour override les paramètres:
- `upload_max_filesize = 3000M`
- `post_max_size = 3000M`
- `max_execution_time = 3600`
- `max_input_time = 3600`
- `memory_limit = 512M`

## 3. Configuration Nginx (Alternative à Apache)

Si vous utilisez Nginx au lieu d'Apache, ajoutez dans votre bloc `server {}`:

```nginx
server {
    # ... autres configurations
    
    # Taille maximale du corps de la requête
    client_max_body_size 3000M;
    
    # Timeout pour recevoir le corps de la requête
    client_body_timeout 3600s;
    
    # Timeout pour FastCGI (PHP-FPM)
    fastcgi_read_timeout 3600s;
    
    # Timeout pour les proxies
    proxy_read_timeout 3600s;
}
```

Puis redémarrez Nginx:
```bash
sudo systemctl restart nginx
```

## 4. Configuration Laravel

### A. Validation dans le Controller

Dans vos controllers d'upload (ex: `VideoController`, `EpisodeController`):

```php
$request->validate([
    'video' => 'required|file|mimetypes:video/mp4,video/avi,video/mpeg|max:3072000', // 3GB en KB
]);
```

### B. Filesystem Configuration

Fichier: `config/filesystems.php`

Assurez-vous que votre driver supporte les gros fichiers:

```php
'local' => [
    'driver' => 'local',
    'root' => storage_path('app'),
    'permissions' => [
        'file' => [
            'public' => 0644,
            'private' => 0600,
        ],
        'dir' => [
            'public' => 0755,
            'private' => 0700,
        ],
    ],
],
```

## 5. Upload en Morceaux (Chunked Upload) - RECOMMANDÉ

Pour les fichiers très volumineux (>2GB), il est fortement recommandé d'utiliser un système d'upload par morceaux:

### Installation de Packages

```bash
composer require pion/laravel-chunk-upload
```

### Exemple d'implémentation

```php
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

public function uploadChunked(Request $request)
{
    $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));
    
    if ($receiver->isUploaded() === false) {
        throw new UploadMissingFileException();
    }
    
    $save = $receiver->receive();
    
    if ($save->isFinished()) {
        // Upload terminé
        return $this->saveFile($save->getFile());
    }
    
    // Upload en cours
    $handler = $save->handler();
    return response()->json([
        "done" => $handler->getPercentageDone(),
        "status" => true
    ]);
}
```

## 6. Intégration Frontend (Flutter)

Pour uploader de gros fichiers depuis Flutter:

```dart
import 'package:dio/dio.dart';

Future<void> uploadVideo(File videoFile) async {
  Dio dio = Dio();
  
  FormData formData = FormData.fromMap({
    "video": await MultipartFile.fromFile(
      videoFile.path,
      filename: "video.mp4",
    ),
  });
  
  Response response = await dio.post(
    'http://yourapi.com/api/upload',
    data: formData,
    onSendProgress: (int sent, int total) {
      print('Progress: ${(sent / total * 100).toStringAsFixed(0)}%');
    },
    options: Options(
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      receiveTimeout: Duration(hours: 1),
      sendTimeout: Duration(hours: 1),
    ),
  );
}
```

## 7. Vérification de la Configuration

### Test PHP

Créez un fichier `info.php` dans `public/`:

```php
<?php
phpinfo();
?>
```

Accédez à `http://localhost/info.php` et vérifiez:
- `upload_max_filesize`
- `post_max_size`
- `max_execution_time`
- `max_input_time`
- `memory_limit`

**⚠️ Supprimez ce fichier après vérification** pour des raisons de sécurité.

### Test via Terminal

```bash
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"
```

## 8. Limites et Recommandations

### Limites Techniques
- **PHP 32-bit**: Limite à 2GB (utilisez PHP 64-bit)
- **Système de fichiers FAT32**: Limite à 4GB (utilisez NTFS/ext4)
- **RAM serveur**: Minimum 2GB recommandé pour fichiers >1GB

### Recommandations
1. **Upload en morceaux**: Pour fichiers >500MB
2. **Compression vidéo**: Optimisez avant upload (H.264, bitrate adaptatif)
3. **CDN**: Utilisez Bunny.net pour la diffusion (déjà configuré)
4. **Stockage**: Vérifiez l'espace disque disponible
5. **Monitoring**: Ajoutez des logs pour tracker les uploads

### Calcul de Bande Passante

**Exemple pour une vidéo de 2GB:**
- Connexion 10 Mbps: ~27 minutes
- Connexion 50 Mbps: ~5.5 minutes
- Connexion 100 Mbps: ~2.7 minutes

## 9. Résolution de Problèmes

### Erreur: "413 Request Entity Too Large"
→ Vérifiez `client_max_body_size` (Nginx) ou `LimitRequestBody` (Apache)

### Erreur: "Maximum execution time exceeded"
→ Augmentez `max_execution_time` et `max_input_time`

### Erreur: "Allowed memory size exhausted"
→ Augmentez `memory_limit` (minimum 512M pour vidéos 2GB+)

### Upload très lent
→ Vérifiez votre connexion internet et considérez l'upload en morceaux

## 10. Checklist de Déploiement

- [ ] Modifier `php.ini` (upload_max_filesize, post_max_size, etc.)
- [ ] Redémarrer Apache/Nginx
- [ ] Vérifier `.htaccess` configuré
- [ ] Tester avec `phpinfo()`
- [ ] Implémenter chunked upload (optionnel mais recommandé)
- [ ] Configurer validation Laravel
- [ ] Tester upload avec fichier test >2GB
- [ ] Vérifier espace disque suffisant
- [ ] Configurer logs de monitoring
- [ ] Mettre en place système de reprise en cas d'échec

## Conclusion

Avec ces configurations, votre système supportera:
- ✅ Séries jusqu'à 30+ minutes (environ 500MB - 2GB)
- ✅ Films jusqu'à 1+ heure (environ 1GB - 3GB)
- ✅ Upload de fichiers jusqu'à 3GB
- ✅ Délai d'exécution de 1 heure

**Note**: Pour la production, envisagez un service de transcodage (AWS MediaConvert, Bunny Stream) pour optimiser automatiquement les vidéos.
