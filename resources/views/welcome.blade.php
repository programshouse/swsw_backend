<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="google-site-verification" content="lRYIuNBgC-AwP-MmMTYZcZDdqOxo1FkRRawocp0vmZg" />
  <title>SWSW</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background: #fff7e8;
      color: #fff;
    }

    .hero {
      min-height: 100vh;
      background: linear-gradient(135deg, #dc3d00, #f05a16);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .hero::before {
      content: "";
      position: absolute;
      width: 520px;
      height: 520px;
      background: #fff7e8;
      border-radius: 50%;
      left: -180px;
      bottom: -160px;
      opacity: 0.95;
    }

    .content {
      position: relative;
      z-index: 2;
      max-width: 850px;
    }

    .logo {
      width: 260px;
      max-width: 80%;
      margin-bottom: 35px;
    }

    h1 {
      font-size: 54px;
      font-weight: 900;
      margin-bottom: 24px;
      letter-spacing: 1px;
    }

    p {
      font-size: 24px;
      line-height: 1.7;
      font-weight: 600;
      color: #fff7e8;
      margin-bottom: 36px;
    }

    .buttons {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .store-btn {
      background: #111;
      color: #fff;
      padding: 12px 22px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 16px;
      font-weight: 700;
    }

    @media (max-width: 768px) {
      h1 {
        font-size: 40px;
      }

      p {
        font-size: 19px;
      }

      .logo {
        width: 210px;
      }
    }
  </style>
</head>

<body>
  <section class="hero">
    <div class="content">
      <!-- Replace with your real logo path -->
      <img src="https://programshouse.com/swsw/public/uploads/settings/1782547392_logo_swsw-logo.jpg" class="logo" />

      <h1>SWSW</h1>

      <p>
        SWSW is a modern food delivery application specialized in delivering
        authentic homemade meals. We connect trusted home kitchens with customers
        looking for fresh, high-quality, and healthy food delivered directly to
        their doorstep.
      </p>

      <div class="buttons">
        <a href="#" class="store-btn">Download on App Store</a>
        <a href="#" class="store-btn">Get it on Google Play</a>
      </div>
    </div>
  </section>
</body>
</html>