<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Euler Rotasyonu - Hula Hoop Çemberler</title>
    <style>
        body { margin: 0; }
        canvas { display: block; }
    </style>
</head>
<body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // Sahne, kamera ve renderer ayarları
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer();
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        // Aydınlatma
        const light = new THREE.AmbientLight(0x404040, 1);
        scene.add(light);

        // Çizilecek çember için materyal (boş hula hoop gibi)
        const material = new THREE.LineBasicMaterial({ color: 0xff0000, linewidth: 2 });

        // 3D çember geometrisi oluştur (kalınlık için torus)
        function createCircle(radius, tubeRadius, radialSegments, tubularSegments, color) {
            const geometry = new THREE.TorusGeometry(radius, tubeRadius, radialSegments, tubularSegments);
            const material = new THREE.MeshBasicMaterial({ color: color, wireframe: true });
            const circle = new THREE.Mesh(geometry, material);
            return circle;
        }

        // Çemberler oluşturuluyor
        const circleX = createCircle(2, 0.1, 64, 64, 0xff0000);  // X eksenine paralel kırmızı çember
        const circleY = createCircle(2, 0.1, 64, 64, 0x00ff00);  // Y eksenine paralel yeşil çember
        const circleZ = createCircle(2, 0.1, 64, 64, 0x0000ff);  // Z eksenine paralel mavi çember

        // Daireleri sahneye ekle
        scene.add(circleX);
        scene.add(circleY);
        scene.add(circleZ);

        // Çemberlerin eksenlerine göre yerleşim ayarları
        circleX.rotation.x = Math.PI / 2;  // X eksenine paralel
        circleY.rotation.y = Math.PI / 2;  // Y eksenine paralel
        circleZ.rotation.z = Math.PI / 2;  // Z eksenine paralel

        // Kamerayı ayarlama
        camera.position.z = 5;

        // Kontroller için değişkenler
        let roll = 0;  // X eksenindeki dönüş
        let pitch = 0;  // Y eksenindeki dönüş
        let yaw = 0;    // Z eksenindeki dönüş

        // Klavye ile kontrol için event listener
        window.addEventListener('keydown', (event) => {
            if (event.key === 'w') roll += 0.1;  // Roll (X ekseni)
            if (event.key === 's') roll -= 0.1;
            if (event.key === 'a') pitch += 0.1;  // Pitch (Y ekseni)
            if (event.key === 'd') pitch -= 0.1;
            if (event.key === 'q') yaw += 0.1;  // Yaw (Z ekseni)
            if (event.key === 'e') yaw -= 0.1;
        });

        // Animasyon fonksiyonu
        function animate() {
            requestAnimationFrame(animate);

            // Euler rotasyonları uygula
            circleX.rotation.x = roll;  // X eksenindeki dönüş
            circleY.rotation.y = pitch;  // Y eksenindeki dönüş
            circleZ.rotation.z = yaw;  // Z eksenindeki dönüş

            // Sahneyi render et
            renderer.render(scene, camera);
        }

        animate();
    </script>
</body>
</html>
