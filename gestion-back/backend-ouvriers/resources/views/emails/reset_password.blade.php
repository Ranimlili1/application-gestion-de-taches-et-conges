<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation mot de passe</title>
</head>
<body style="font-family: Arial, sans-serif">

    <h2>Réinitialisation de votre mot de passe</h2>

    <p>Bonjour,</p>

    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>

    <p>
        <a href="{{ $resetUrl }}"
           style="display:inline-block;
                  padding:10px 15px;
                  background:#2563eb;
                  color:white;
                  text-decoration:none;
                  border-radius:6px;">
            Réinitialiser mon mot de passe
        </a>
    </p>

    <p>Ce lien expire dans <strong>60 minutes</strong>.</p>

    <p>Si vous n’avez pas fait cette demande, ignorez cet email.</p>

    <hr>
    <small>Gestion Ouvriers</small>

</body>
</html>
