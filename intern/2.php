<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Form Submission Result</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f0f8ff, #e0f7fa);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        h2 {
            text-align: center;
            color: #2e7d32;
            font-size: 28px;
            margin-bottom: 30px;
        }

        p {
            justify-content:center;
            align-items:center;
            font-size: 17px;
            margin: 12px 0;
            line-height: 1.5;
            color: #333;
        }

        .label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
            color: #1a237e;
        }

        @media screen and (max-width: 600px) {
            .container {
                margin: 30px 15px;
                padding: 25px;
            }

            .label {
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Submitted Successfully!</h2>
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <p><span class="label">First Name:</span> <?= htmlspecialchars($_POST['firstName']) ?></p>
        <p><span class="label">Last Name:</span> <?= htmlspecialchars($_POST['lastName']) ?></p>
        <p><span class="label">Username:</span> <?= htmlspecialchars($_POST['username']) ?></p>
        <p><span class="label">Password:</span> <?= str_repeat('*', strlen($_POST['password'])) ?></p>
        <p><span class="label">Date of Birth:</span> <?= htmlspecialchars($_POST['dob']) ?></p>
        <p><span class="label">Email:</span> <?= htmlspecialchars($_POST['email']) ?></p>
        <p><span class="label">Phone:</span> <?= htmlspecialchars($_POST['phone']) ?></p>
        <p><span class="label">Address:</span> <?= nl2br(htmlspecialchars($_POST['address'])) ?></p>
    <?php else: ?>
        <p style="text-align: center; color: red;">Invalid access.</p>
    <?php endif; ?>
</div>

</body>
</html>
