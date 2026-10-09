
</main>

<footer class="pb-footer">
  <div class="container-xl">
    <div class="footer-grid">
      <div class="footer-brand">
        <h3><i class="fas fa-paw" style="color:var(--pb-teal);margin-right:8px;"></i><?= e(setting('site_name', 'PawBridge Rescue')) ?></h3>
        <p><?= e(setting('site_tagline', 'HELPING ANIMALS MOVE FROM DANGER TO SAFETY')) ?>. Every animal deserves safety, care, and a chance to thrive.</p>
        <div class="footer-socials">
          <?php if (setting('facebook_url')): ?><a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
          <?php if (setting('twitter_url')): ?><a href="<?= e(setting('twitter_url')) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a><?php endif; ?>
          <?php if (setting('instagram_url')): ?><a href="<?= e(setting('instagram_url')) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
          <?php if (setting('youtube_url')): ?><a href="<?= e(setting('youtube_url')) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a><?php endif; ?>
          <?php if (setting('linkedin_url')): ?><a href="<?= e(setting('linkedin_url')) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
        </div>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul>
          <li><a href="<?= url('about.php') ?>">About Us</a></li>
          <li><a href="<?= url('about.php#team') ?>">Our Team</a></li>
          <li><a href="<?= url('about.php#impact') ?>">Our Impact</a></li>
          <li><a href="<?= url('animals.php') ?>">Adopt a Pet</a></li>
          <li><a href="<?= url('stories.php') ?>">Rescue Stories</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Get Involved</h4>
        <ul>
          <li><a href="<?= url('foster.php') ?>">Foster Program</a></li>
          <li><a href="<?= url('volunteer.php') ?>">Volunteer</a></li>
          <li><a href="<?= url('donate.php') ?>">Make a Donation</a></li>
          <li><a href="<?= url('events.php') ?>">Upcoming Events</a></li>
          <li><a href="<?= url('blog.php') ?>">Blog &amp; Tips</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Get in Touch</h4>
        <ul class="footer-contact" style="list-style:none;padding:0;">
          <li><i class="fas fa-envelope"></i> <span><?= e(setting('site_email', 'hello@pawbridge.org')) ?></span></li>
          <li><i class="fas fa-phone"></i> <span><?= e(setting('site_phone', '+1 (555) 123-4567')) ?></span></li>
          <li><i class="fas fa-location-dot"></i> <span><?= e(trim(setting('site_address','123 Rescue Lane').', '.setting('site_city','Animal City')), ', ') ?></span></li>
        </ul>
        <div style="margin-top:26px;">
          <h4 style="margin-bottom:14px;">Stay Updated</h4>
          <form method="POST" action="<?= url('subscribe.php') ?>" class="newsletter-form" onsubmit="handleNewsletter(event)">
            <input type="email" name="email" placeholder="Your email address" required>
            <button type="submit" aria-label="Subscribe"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p><?= e(setting('footer_text', '© 2026 PawBridge Rescue. All rights reserved. Built with 💚 for animals.')) ?></p>
      <p style="display:flex;gap:20px;flex-wrap:wrap;">
        <a href="<?= url('contact.php') ?>" style="color:rgba(255,255,255,0.5);">Contact</a>
        <a href="<?= url('blog.php') ?>" style="color:rgba(255,255,255,0.5);">Blog</a>
        <a href="<?= url('admin/login.php') ?>" style="color:rgba(255,255,255,0.5);">Admin</a>
      </p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset_url('js/main.js') ?>"></script>
<script>
function handleNewsletter(e) {
  e.preventDefault();
  var f = e.target;
  var fd = new FormData(f);
  fd.append('csrf_token', '<?= generate_csrf_token() ?>');
  fetch(f.action, {method:'POST', body: fd})
    .then(r => r.json())
    .then(d => {
      alert(d.message || (d.success ? 'Thanks for subscribing!' : 'Something went wrong.'));
      if (d.success) f.reset();
    }).catch(() => alert('Subscribed!'));
}
</script>
</body>
</html>
