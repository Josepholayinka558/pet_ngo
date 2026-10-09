<?php
$pageMetaTitle = 'Contact Us - PawBridge Rescue';
$pageMetaDesc = 'Get in touch with PawBridge Rescue. Contact us via email, phone, or visit us. We\'re here to help with adoptions, fostering, volunteering, and donations.';
require_once __DIR__ . '/includes/header.php';

if (is_post() && isset($_POST['submit_contact'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token.');
    } else {
        $data = [
            'full_name' => clean_input($_POST['full_name']),
            'email' => clean_input($_POST['email']),
            'phone' => clean_input($_POST['phone'] ?? ''),
            'subject' => clean_input($_POST['subject'] ?? ''),
            'message' => clean_input($_POST['message']),
            'is_read' => 0,
        ];
        if (empty($data['full_name']) || empty($data['email']) || empty($data['message'])) {
            flash('error', 'Please fill in all required fields.');
        } elseif (!validate_email($data['email'])) {
            flash('error', 'Please enter a valid email address.');
        } else {
            pdo_insert('contact_messages', $data);
            flash('success', 'Thank you for reaching out! Your message has been sent and our team will get back to you within 2-3 business days.');
            redirect('contact.php#sent');
        }
    }
}
?>
<section class="page-hero">
    <span class="ph-eyebrow" style="background:var(--pb-mint);color:var(--pb-primary);">We're Here to Help</span>
    <h1>Get in <em style="color:var(--pb-teal);font-style:italic;">Touch</em></h1>
    <p>Have a question about adoption, fostering, volunteering, or donations? Want to report an animal in need? We'd love to hear from you.</p>
</section>

<section class="section" id="sent">
    <div class="container-xl">
        <?= display_flash() ?>
        <div class="contact-grid">
            <div>
                <div class="contact-info-block">
                    <h3>Contact Information</h3>
                    <div class="ci-item">
                        <div class="ci-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h5>Email</h5>
                            <p><?= e(setting('site_email', 'hello@pawbridge.org')) ?></p>
                            <p style="font-size:0.82rem;opacity:0.8;margin-top:4px;">We reply within 2-3 business days</p>
                        </div>
                    </div>
                    <div class="ci-item">
                        <div class="ci-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h5>Phone</h5>
                            <p><?= e(setting('site_phone', '+1 (555) 123-4567')) ?></p>
                            <p style="font-size:0.82rem;opacity:0.8;margin-top:4px;">For emergencies, please call 24/7 rescue hotline</p>
                        </div>
                    </div>
                    <div class="ci-item">
                        <div class="ci-icon"><i class="fas fa-location-dot"></i></div>
                        <div>
                            <h5>Visit Us</h5>
                            <p><?= e(setting('site_address', '123 Rescue Lane')) ?></p>
                            <p style="margin:0;"><?= e(trim(setting('site_city', 'Animal City').', '.setting('site_country', 'USA')), ', ') ?></p>
                            <p style="font-size:0.82rem;opacity:0.8;margin-top:4px;">By appointment only for meet-and-greets</p>
                        </div>
                    </div>
                    <div class="ci-item" style="border-bottom:none;">
                        <div class="ci-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h5>Office Hours</h5>
                            <?php foreach (explode("\n", setting('contact_hours', "Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed")) as $line): ?>
                                <p style="margin:2px 0;"><?= e($line) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top:28px;background:white;border-radius:var(--radius-xl);padding:28px;box-shadow:var(--shadow-sm);">
                    <h4 style="margin-bottom:16px;"><i class="fas fa-share-nodes" style="color:var(--pb-teal);"></i> Follow Our Journey</h4>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <?php if (setting('facebook_url')): ?><a href="<?= e(setting('facebook_url')) ?>" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:50%;background:#1877F2;color:white;display:flex;align-items:center;justify-content:center;"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                        <?php if (setting('twitter_url')): ?><a href="<?= e(setting('twitter_url')) ?>" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:50%;background:#1DA1F2;color:white;display:flex;align-items:center;justify-content:center;"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if (setting('instagram_url')): ?><a href="<?= e(setting('instagram_url')) ?>" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#833AB4,#FD1D1D,#FCAF45);color:white;display:flex;align-items:center;justify-content:center;"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if (setting('youtube_url')): ?><a href="<?= e(setting('youtube_url')) ?>" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:50%;background:#FF0000;color:white;display:flex;align-items:center;justify-content:center;"><i class="fab fa-youtube"></i></a><?php endif; ?>
                        <?php if (setting('linkedin_url')): ?><a href="<?= e(setting('linkedin_url')) ?>" target="_blank" rel="noopener" style="width:44px;height:44px;border-radius:50%;background:#0A66C2;color:white;display:flex;align-items:center;justify-content:center;"><i class="fab fa-linkedin-in"></i></a><?php endif; ?>
                    </div>
                </div>

                <?php if (setting('map_embed')): ?>
                <div style="margin-top:28px;border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-md);">
                    <?= setting('map_embed') ?>
                </div>
                <?php else: ?>
                <div style="margin-top:28px;border-radius:var(--radius-xl);overflow:hidden;box-shadow:var(--shadow-md);height:300px;background:var(--pb-mint-gradient);display:flex;align-items:center;justify-content:center;flex-direction:column;text-align:center;padding:30px;">
                    <i class="fas fa-map-location-dot" style="font-size:48px;color:var(--pb-secondary);margin-bottom:14px;opacity:0.6;"></i>
                    <h4 style="margin-bottom:8px;">Visit Our Rescue Center</h4>
                    <p style="max-width:400px;"><?= e(setting('site_address', '123 Rescue Lane').', '.setting('site_city', 'Animal City').', '.setting('site_country', 'United States')) ?></p>
                    <a href="https://www.google.com/maps/search/<?= urlencode(setting('site_address', '123 Rescue Lane').' '.setting('site_city', '')) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm" style="margin-top:16px;"><i class="fas fa-directions"></i> Get Directions</a>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="form-card">
                    <h3>Send Us a Message</h3>
                    <p style="color:var(--pb-gray);margin-bottom:28px;">Fields marked with <span style="color:var(--pb-danger);">*</span> are required</p>
                    <form method="POST" class="needs-validation" novalidate>
                        <?= csrf_field() ?>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Your Name <span style="color:var(--pb-danger);">*</span></label>
                                <input type="text" name="full_name" class="form-control" required placeholder="Jane Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address <span style="color:var(--pb-danger);">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="jane@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone (optional)</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select name="subject" class="form-select">
                                    <option value="">Select a topic...</option>
                                    <option>Adoption Inquiry</option>
                                    <option>Foster Question</option>
                                    <option>Volunteering</option>
                                    <option>Donation Question</option>
                                    <option>Report Animal in Need</option>
                                    <option>Event Question</option>
                                    <option>Media / Press</option>
                                    <option>General Question</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Your Message <span style="color:var(--pb-danger);">*</span></label>
                                <textarea name="message" class="form-control" rows="7" required placeholder="Tell us how we can help..."></textarea>
                            </div>
                        </div>

                        <div style="padding:22px;background:var(--pb-soft-bg);border-radius:var(--radius-md);margin-bottom:28px;">
                            <p style="margin:0;font-size:0.88rem;color:var(--pb-dark-gray);"><i class="fas fa-shield-halved" style="color:var(--pb-secondary);margin-right:8px;"></i> We never share your personal information. Your details are used only to respond to your inquiry.</p>
                        </div>

                        <button type="submit" name="submit_contact" class="btn btn-primary btn-lg w-100 justify-content-center">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--pb-soft-bg);">
    <div class="container-xl">
        <div class="pb-section-title">
            <span class="eyebrow">Quick Links</span>
            <h2>You May Also <em>Want to Know</em></h2>
        </div>
        <div class="row g-4">
            <div class="col-md-3" onclick="window.location='<?= url('animals.php') ?>'" style="cursor:pointer;">
                <div style="background:white;padding:32px;border-radius:var(--radius-lg);height:100%;transition:all 0.3s;box-shadow:var(--shadow-sm);" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='none';">
                    <div style="width:56px;height:56px;border-radius:16px;background:var(--pb-mint);display:flex;align-items:center;justify-content:center;color:var(--pb-primary);font-size:24px;margin-bottom:18px;"><i class="fas fa-dog"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;">Adopt a Pet</h4>
                    <p style="font-size:0.9rem;color:var(--pb-gray);margin:0;">Browse our adorable animals waiting for their forever homes.</p>
                </div>
            </div>
            <div class="col-md-3" onclick="window.location='<?= url('foster.php') ?>'" style="cursor:pointer;">
                <div style="background:white;padding:32px;border-radius:var(--radius-lg);height:100%;transition:all 0.3s;box-shadow:var(--shadow-sm);" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='none';">
                    <div style="width:56px;height:56px;border-radius:16px;background:#DBFEFE;display:flex;align-items:center;justify-content:center;color:var(--pb-teal);font-size:24px;margin-bottom:18px;"><i class="fas fa-house-heart"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;">Become a Foster</h4>
                    <p style="font-size:0.9rem;color:var(--pb-gray);margin:0;">Give an animal a safe temporary place to heal.</p>
                </div>
            </div>
            <div class="col-md-3" onclick="window.location='<?= url('donate.php') ?>'" style="cursor:pointer;">
                <div style="background:white;padding:32px;border-radius:var(--radius-lg);height:100%;transition:all 0.3s;box-shadow:var(--shadow-sm);" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='none';">
                    <div style="width:56px;height:56px;border-radius:16px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;color:var(--pb-accent);font-size:24px;margin-bottom:18px;"><i class="fas fa-heart"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;">Make a Donation</h4>
                    <p style="font-size:0.9rem;color:var(--pb-gray);margin:0;">Your generosity directly saves animal lives every day.</p>
                </div>
            </div>
            <div class="col-md-3" onclick="window.location='<?= url('faq') ?>'" style="cursor:pointer;">
                <div style="background:white;padding:32px;border-radius:var(--radius-lg);height:100%;transition:all 0.3s;box-shadow:var(--shadow-sm);" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='none';">
                    <div style="width:56px;height:56px;border-radius:16px;background:#DBEAFE;display:flex;align-items:center;justify-content:center;color:var(--pb-navy);font-size:24px;margin-bottom:18px;"><i class="fas fa-circle-question"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;">FAQ</h4>
                    <p style="font-size:0.9rem;color:var(--pb-gray);margin:0;">Answers to our most commonly asked questions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
