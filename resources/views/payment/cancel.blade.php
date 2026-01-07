<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Annulé</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 400px;
        }
        .cancel-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f44336;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .cancel-icon::before {
            content: '✕';
            color: white;
            font-size: 50px;
            font-weight: bold;
        }
        h1 {
            color: #333;
            margin: 0 0 10px;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="cancel-icon"></div>
        <h1>Paiement Annulé</h1>
        <p>Votre paiement a été annulé.</p>
        <p>Aucun frais n'a été débité.</p>
        <p style="font-size: 14px; color: #999; margin-top: 20px;">Vous pouvez fermer cette fenêtre et réessayer.</p>
    </div>
</body>
</html>
