<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çember Rotasyonu</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        canvas {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <canvas id="canvas" width="500" height="500"></canvas>

    <script>
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');

        let angle1 = 0, angle2 = 0, angle3 = 0;

        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;

        const circle1 = { radius: 50, x: centerX, y: centerY };
        const circle2 = { radius: 40, x: centerX + 100, y: centerY };
        const circle3 = { radius: 30, x: centerX + 200, y: centerY };

        function drawCircle(x, y, radius, color) {
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.fillStyle = color;
            ctx.fill();
            ctx.stroke();
        }

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Çemberlerin merkezi etrafında dönüşü
            ctx.save();

            // Çember 1 rotasyonu
            ctx.translate(centerX, centerY); // Çevirmeye başla
            ctx.rotate(angle1);
            drawCircle(circle1.x - centerX, circle1.y - centerY, circle1.radius, 'red');

            // Çember 2 rotasyonu
            ctx.translate(100, 0); // Çember 2'yi yerleştir
            ctx.rotate(angle2);
            drawCircle(circle2.x - (centerX + 100), circle2.y - centerY, circle2.radius, 'blue');

            // Çember 3 rotasyonu
            ctx.translate(100, 0); // Çember 3'ü yerleştir
            ctx.rotate(angle3);
            drawCircle(circle3.x - (centerX + 200), circle3.y - centerY, circle3.radius, 'green');

            ctx.restore();

            angle1 += 0.01;
            angle2 += 0.02;
            angle3 += 0.03;

            requestAnimationFrame(draw); // Animasyon için fonksiyonu tekrar çağır
        }

        draw();
    </script>
</body>
</html>
