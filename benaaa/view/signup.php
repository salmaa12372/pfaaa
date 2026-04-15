<?php 
include("../config/database.php");
include("../controller/traitement.php");
session_start();

// Vérifier si l'utilisateur est déjà connecté
if(isset($_SESSION['email'])){
    header("Location: User_profile.php");
    exit();
}

$message = "";
$messageType = "";

// Traitement de l'inscription
if (!empty($_POST) && isset($_POST['user_name'], $_POST['email'], $_POST['password'])) {
    $result = AddUser($cnx, $_POST);
    if ($result === true) {
        $message = "✨ Account created successfully! Please login.";
        $messageType = "success";
    } else {
        $message = $result;
        $messageType = "error";
    }
}

// Traitement de la connexion
if (!empty($_POST) && isset($_POST['login_email'], $_POST['login_password'])) {
    $loginData = [
        'email' => $_POST['login_email'],
        'password' => $_POST['login_password']
    ];
    $user = ConnectUser($cnx, $loginData);
    if ($user) {
        $_SESSION['nom'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['id_user'] = $user['id_user'];
        header("Location: User_profile.php");
        exit();
    } else {
        $message = "❌ Invalid email or password";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Join Our Community</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <div class="hero-particles">
        <div class="hparticle p1"></div>
        <div class="hparticle p2"></div>
        <div class="hparticle p3"></div>
        <div class="hparticle p4"></div>
        <div class="hparticle p5"></div>
        <div class="hparticle p6"></div>
    </div>

    <div class="toggle-container">
        <div class="toggle-wrap">
            <input type="checkbox" id="darkmode-toggle" class="toggle-input">
            <label for="darkmode-toggle" class="toggle-track">
                <div class="toggle-thumb"></div>
            </label>
        </div>
    </div>

    <div class="wrapper">
        <div class="container" id="container">
            <!-- SIGN UP FORM -->
            <div class="form-container sign-up-container">
                <form method="POST" action="" id="signupForm">
                    <h1>Create Account</h1>
                    <div class="bar"></div>
                    <div class="socials">
                        <a href="#">G</a>
                        <a href="#">f</a>
                        <a href="#">in</a>
                        <a href="#">𝕏</a>
                    </div>
                    <div class="or">or use your email for registration</div>
                    
                    <?php if ($message && $messageType === 'success'): ?>
                        <div class="message-box success"><?= htmlspecialchars($message) ?></div>
                    <?php elseif ($message && $messageType === 'error'): ?>
                        <div class="message-box error"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    
                    <div class="field">
                        <span>👤</span>
                        <input type="text" id="signupName" name="user_name" placeholder="Full Name" required>
                    </div>
                    <div class="field">
                        <span>📧</span>
                        <input type="email" id="signupEmail" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="field">
                        <span>🔒</span>
                        <input type="password" id="signupPassword" name="password" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword('signupPassword', this)">👁️</span>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                    <div class="field">
                        <span>✓</span>
                        <input type="password" id="signupConfirmPassword" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn-submit" id="signupBtn">Sign Up</button>
                </form>
            </div>

            <!-- SIGN IN FORM -->
            <div class="form-container sign-in-container">
                <form method="POST" action="" id="signinForm">
                    <h1>Sign In</h1>
                    <div class="bar"></div>
                    <div class="socials">
                        <a href="#">G</a>
                        <a href="#">f</a>
                        <a href="#">in</a>
                        <a href="#">𝕏</a>
                    </div>
                    <div class="or">or use your email password</div>
                    <div class="field">
                        <span>📧</span>
                        <input type="email" id="signinEmail" name="login_email" placeholder="Email" required>
                    </div>
                    <div class="field">
                        <span>🔒</span>
                        <input type="password" id="signinPassword" name="login_password" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword('signinPassword', this)">👁️</span>
                    </div>
                    <a href="#" class="forgot" id="forgotPassword">Forgot your password?</a>
                    <button type="submit" class="btn-submit" id="signinBtn">Sign In</button>
                </form>
            </div>

            <!-- OVERLAY -->
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>Welcome Back!</h1>
                        <p>Enter your personal details to use all of site features</p>
                        <button class="btn-ghost" id="signInBtn">Sign In</button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>Hello Friend!</h1>
                        <p>Register with your personal details to use all of site features</p>
                        <button class="btn-ghost" id="signUpBtn">Sign Up</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Toggle
        const toggleCheckbox = document.getElementById('darkmode-toggle');
        
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark');
            toggleCheckbox.checked = true;
        }
        
        toggleCheckbox.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.body.classList.remove('dark');
                localStorage.setItem('darkMode', 'disabled');
            }
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');
            
            if (!bar) return;
            
            if (password.length === 0) {
                bar.style.width = '0%';
                text.textContent = '';
                return;
            }
            
            switch(strength) {
                case 1:
                    bar.style.width = '25%';
                    bar.style.background = 'var(--red)';
                    text.textContent = 'Weak';
                    break;
                case 2:
                    bar.style.width = '50%';
                    bar.style.background = 'var(--gold)';
                    text.textContent = 'Medium';
                    break;
                case 3:
                    bar.style.width = '75%';
                    bar.style.background = 'var(--green-light)';
                    text.textContent = 'Good';
                    break;
                case 4:
                    bar.style.width = '100%';
                    bar.style.background = 'var(--green)';
                    text.textContent = 'Strong';
                    break;
            }
        }

        // Toggle password visibility
        window.togglePassword = function(inputId, element) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                element.textContent = '🙈';
            } else {
                input.type = 'password';
                element.textContent = '👁️';
            }
        }

        // Validate email format
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Signup form handler
        const signupForm = document.getElementById('signupForm');
        const signupPassword = document.getElementById('signupPassword');
        const signupConfirm = document.getElementById('signupConfirmPassword');
        const signupBtn = document.getElementById('signupBtn');

        signupPassword.addEventListener('input', (e) => {
            checkPasswordStrength(e.target.value);
        });

        signupForm.addEventListener('submit', (e) => {
            const name = document.getElementById('signupName').value.trim();
            const email = document.getElementById('signupEmail').value.trim();
            const password = signupPassword.value;
            const confirm = signupConfirm.value;
            
            if (!name || !email || !password || !confirm) {
                e.preventDefault();
                showToast('Please fill in all fields', 'error');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showToast('Please enter a valid email address', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showToast('Password must be at least 6 characters', 'error');
                return false;
            }
            
            if (password !== confirm) {
                e.preventDefault();
                showToast('Passwords do not match', 'error');
                return false;
            }
            
            // Show loading state
            signupBtn.classList.add('loading');
            signupBtn.disabled = true;
            signupBtn.textContent = '';
            
            return true;
        });

        // Signin form handler
        const signinForm = document.getElementById('signinForm');
        const signinBtn = document.getElementById('signinBtn');

        signinForm.addEventListener('submit', (e) => {
            const email = document.getElementById('signinEmail').value.trim();
            const password = document.getElementById('signinPassword').value;
            
            if (!email || !password) {
                e.preventDefault();
                showToast('Please fill in all fields', 'error');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showToast('Please enter a valid email address', 'error');
                return false;
            }
            
            // Show loading state
            signinBtn.classList.add('loading');
            signinBtn.disabled = true;
            signinBtn.textContent = '';
            
            return true;
        });

        // Forgot password handler
        document.getElementById('forgotPassword').addEventListener('click', (e) => {
            e.preventDefault();
            showToast('Password reset link sent to your email!', 'info');
        });

        // Card transition
        const container = document.getElementById('container');
        const signUpBtn = document.getElementById('signUpBtn');
        const signInBtn = document.getElementById('signInBtn');

        signUpBtn.addEventListener('click', () => {
            container.classList.add('active');
        });

        signInBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });

        // Social icons handler
        document.querySelectorAll('.socials a').forEach(icon => {
            icon.addEventListener('click', (e) => {
                e.preventDefault();
                const platform = icon.innerText;
                showToast(`🔗 ${platform} login coming soon!`, 'info');
            });
        });

        // Real-time validation for signup email
        const signupEmail = document.getElementById('signupEmail');
        signupEmail.addEventListener('blur', () => {
            if (signupEmail.value && !isValidEmail(signupEmail.value)) {
                signupEmail.classList.add('error');
                showToast('Please enter a valid email', 'error');
            } else {
                signupEmail.classList.remove('error');
            }
        });

        signupEmail.addEventListener('input', () => {
            signupEmail.classList.remove('error');
        });

        // Real-time password match validation
        signupConfirm.addEventListener('input', () => {
            if (signupConfirm.value && signupConfirm.value !== signupPassword.value) {
                signupConfirm.classList.add('error');
            } else {
                signupConfirm.classList.remove('error');
            }
        });

        // Save form data to localStorage (auto-save)
        const signupFields = ['signupName', 'signupEmail'];
        signupFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            const saved = localStorage.getItem(fieldId);
            if (saved) field.value = saved;
            
            field.addEventListener('input', () => {
                localStorage.setItem(fieldId, field.value);
            });
        });

        // Load animation
        window.addEventListener('load', () => {
            const wrapper = document.querySelector('.wrapper');
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'translateY(30px)';
            setTimeout(() => {
                wrapper.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                wrapper.style.opacity = '1';
                wrapper.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>