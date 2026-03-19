</div>
<!-- End Main Content Wrapper -->

<!-- Toast Notification -->
<div id="toast"
  class="fixed bottom-4 right-4 glass-panel px-4 py-2 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
  <span id="toastMessage" class="text-emerald-800">Copied to clipboard!</span>
</div>

<!-- Particle Animation Script -->
<script>
  class Particle {
    constructor(x, y, canvas) {
      this.x = x;
      this.y = y;
      this.canvas = canvas;
      this.size = Math.random() * 2 + 1;
      this.speedX = (Math.random() - 0.5) * 1.5;
      this.speedY = (Math.random() - 0.5) * 1.5;
      this.opacity = Math.random() * 0.5 + 0.5;
      this.pulsePhase = Math.random() * Math.PI * 2;
    }
    update() {
      this.x += this.speedX;
      this.y += this.speedY;
      if (this.x < 0) this.x = this.canvas.width;
      if (this.x > this.canvas.width) this.x = 0;
      if (this.y < 0) this.y = this.canvas.height;
      if (this.y > this.canvas.height) this.y = 0;
      this.pulsePhase += 0.02;
      this.opacity = Math.sin(this.pulsePhase) * 0.25 + 0.85;
    }
    draw(ctx) {
      ctx.fillStyle = `rgba(6, 78, 59, ${this.opacity})`;
      ctx.shadowColor = 'transparent';
      ctx.shadowBlur = 0;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fill();
    }
  }

  const canvas = document.getElementById('particleCanvas');
  const ctx = canvas.getContext('2d');
  let particles = [];
  let animationId;

  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }

  function initParticles() {
    particles = [];
    const particleCount = Math.floor((canvas.width * canvas.height) / 12000);
    for (let i = 0; i < particleCount; i++) {
      const x = Math.random() * canvas.width;
      const y = Math.random() * canvas.height;
      particles.push(new Particle(x, y, canvas));
    }
  }

  function drawConnections() {
    ctx.strokeStyle = 'rgba(6, 78, 59, 0.5)';
    ctx.lineWidth = 1.5;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const distance = Math.sqrt(dx * dx + dy * dy);
        if (distance < 150) {
          const opacity = (1 - distance / 150) * 0.4;
          ctx.strokeStyle = `rgba(6, 78, 59, ${opacity})`;
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.stroke();
        }
      }
    }
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < particles.length; i++) {
      particles[i].update();
      particles[i].draw(ctx);
    }
    drawConnections();
    animationId = requestAnimationFrame(animate);
  }

  // Initialize particles
  resizeCanvas();
  initParticles();
  animate();

  window.addEventListener('resize', () => {
    resizeCanvas();
    initParticles();
  });
</script>
</body>

</html>