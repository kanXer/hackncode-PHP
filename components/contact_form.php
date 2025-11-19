<?php
if (!isset($errors)) $errors = [];
if (!isset($success_message)) $success_message = '';
?>

<section class="contact-section card" id="contact">
  <h2>Contact</h2>

  <?php if(!empty($errors)): ?>
    <div class="api-error-box">
      <strong>Validation Errors:</strong>
      <ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
    </div>
  <?php elseif(!empty($success_message)): ?>
    <?php if(strpos($success_message,'ERROR:')!==false): ?>
      <div class="api-error-box"><p><?=nl2br(htmlspecialchars($success_message))?></p></div>
    <?php else: ?>
      <div class="btn" style="margin-top:10px; text-align:center; display:block;"><?=htmlspecialchars($success_message)?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="contact-grid">
    <div>
      <p class="small">Want to work together? Fill this form or email me at <strong style="color:var(--accent)">user.kanxer@gmail.com</strong></p>
      <form method="post" id="contactForm" novalidate>
        <label for="name">Name</label>
        <input id="name" name="name" required value="<?=htmlspecialchars($_POST['name'] ?? '')?>">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="<?=htmlspecialchars($_POST['email'] ?? '')?>">
        <label for="message">Message</label>
        <textarea id="message" name="message" required><?=htmlspecialchars($_POST['message'] ?? '')?></textarea>
        <div style="margin-top:10px">
          <button type="submit" name="contact_submit" class="btn">Send Message</button>
        </div>
      </form>
    </div>

    <div class="card " style="margin:2px padding:12px">
      <div style="justify-content:center; display:flex; flex-direction:column; align-items:center;">
        <h3>Contact Info</h3>
        <p class="small">Email: user.kanxer@gmail.com</p>
        <p class="small">Phone: +91 9696262007</p>
        <p class="small">Location: Uttar Pradesh, India</p>
      </div>
      <hr style="opacity:0.06;margin:12px 0">
      <h3 style="text-align:center;margin-top:10px;">Follow Me On</h3>
      <div class="social-icons" style="justify-content:center; display:flex; gap:36px; margin-top:0;">
        <a href="https://facebook.com/sahil.srivastava.1004" target="_blank"><i class="fab fa-facebook"></i></a>
        <a href="https://instagram.com/p.c.kill3r" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://wa.me/919696262007" target="_blank"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>
  </div>
</section>
