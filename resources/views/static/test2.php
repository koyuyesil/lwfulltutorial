<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Euler Rotasyonu - Roll, Pitch, Yaw</title>
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

        // Çizilecek daireler için materyaller
        const materialX = new THREE.LineBasicMaterial({ color: 0xff0000 });
        const materialY = new THREE.LineBasicMaterial({ color: 0x00ff00 });
        const materialZ = new THREE.LineBasicMaterial({ color: 0x0000ff });

        // Çember geometrisi oluştur
        function createCircle(radius, segments, color) {
            const geometry = new THREE.CircleGeometry(radius, segments);
            const material = new THREE.MeshBasicMaterial({ color: color, side: THREE.DoubleSide });
            const circle = new THREE.Mesh(geometry, material);
            return circle;
        }

        // Dairelerin oluşturulması
        const circleX = createCircle(2, 64, 0xff0000);  // X ekseni için kırmızı daire
        const circleY = createCircle(2, 64, 0x00ff00);  // Y ekseni için yeşil daire
        const circleZ = createCircle(2, 64, 0x0000ff);  // Z ekseni için mavi daire

        // Daireleri sahneye ekle
        scene.add(circleX);
        scene.add(circleY);
        scene.add(circleZ);

        // Dairelerin eksenlerine göre yerleşim ayarları
        circleX.rotation.x = Math.PI / 2;  // X eksenine paralel
        circleY.rotation.y = Math.PI / 2;  // Y eksenine paralel
        circleZ.rotation.z = Math.PI / 2;  // Z eksenine paralel

        // Kamerayı ayarlama
        camera.position.z = 5;

        // 0'ı gösteren yazılar için bir fonksiyon
        function createText(text, position) {
            const loader = new THREE.FontLoader();
            loader.load('https://threejs.org/examples/fonts/helvetiker_regular.typeface.json', function(font) {
                const geometry = new THREE.TextGeometry(text, {
                    font: font,
                    size: 0.1,
                    height: 0.01
                });
                const material = new THREE.MeshBasicMaterial({ color: 0xffffff });
                const mesh = new THREE.Mesh(geometry, material);
                mesh.position.set(position.x, position.y, position.z);
                scene.add(mesh);
            });
        }

        // 0'ları yazma
        createText('0', new THREE.Vector3(2, 0, 0));  // X ekseni
        createText('0', new THREE.Vector3(0, 2, 0));  // Y ekseni
        createText('0', new THREE.Vector3(0, 0, 2));  // Z ekseni

        // Kontroller için değişkenler
        let roll = 0;
        let pitch = 0;
        let yaw = 0;

        // Klavye ile kontrol için event listener
        window.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowUp') pitch += 0.1;  // Pitch
            if (event.key === 'ArrowDown') pitch -= 0.1;
            if (event.key === 'ArrowLeft') yaw -= 0.1;  // Yaw
            if (event.key === 'ArrowRight') yaw += 0.1;
            if (event.key === 'w') roll += 0.1;  // Roll
            if (event.key === 's') roll -= 0.1;
        });

        // Animasyon
        function animate() {
            requestAnimationFrame(animate);

            // Euler rotasyonları uygula
            circleX.rotation.x = roll;
            circleY.rotation.y = pitch;
            circleZ.rotation.z = yaw;

            // Sahneyi render et
            renderer.render(scene, camera);
        }

        animate();
    </script>
</body>
</html>
