<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>D_BLOGGER Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome CDN -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="icon" type="image/x-icon" href="./favicon.jpg">
</head>

<body class="min-h-screen flex flex-col md:flex-row bg-black text-white">
  <!-- Left Section -->
  <div
    class="relative w-full md:w-1/2 h-[400px] md:h-auto flex flex-col items-center justify-center p-10 overflow-hidden">
    <!-- Background Video -->
    <video
      autoplay
      loop
      muted
      playsinline
      class="absolute inset-0 w-full h-full object-cover z-0">
      <source src="small-vecteezy_simple-black-background-animation-with-gently-moving-white_21224133_small.mp4" type="video/mp4" />
      Your browser does not support the video tag.
    </video>

    <!-- Dark Overlay (for text readability) -->
    <div class="absolute inset-0 bg-black/70 z-10"></div>

    <!-- Content -->
    <div class="relative z-20 flex flex-col items-center text-center">
      <!-- Platform Name -->
      <h1
        class="text-5xl md:text-6xl font-extrabold mb-4 text-cyan-400 tracking-wider"
        style="font-family: 'Orbitron', sans-serif">
        D_<span class="text-white">BLOGGER</span>
      </h1>

      <!-- Tagline -->
      <p class="text-gray-300 text-lg md:text-xl max-w-sm">
        Login To Share Your Story...
      </p>
    </div>
  </div>

  <!-- Right Section -->
  <div
    class="w-full md:w-1/2 flex items-center justify-center p-6 md:p-12 bg-gray-900">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-2xl shadow-lg">
      <h2 class="text-3xl font-bold mb-8 text-cyan-400 text-center">Login</h2>

      <form class="space-y-5" action="logauth.php" method="POST">
        <!-- Email -->
        <div>

          <label class="block text-sm font-semibold mb-1"><i class="fa-solid fa-user text-white"></i> Email:</label>
          <input
            type="email"
            placeholder="Enter your Email"
            name="email"
            class="w-full p-3 rounded-md bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-400" />
        </div>

        <div class="w-full">
          <label class="block text-sm font-semibold mb-1">
            <i class="fa-solid fa-key text-white"></i> Password:
          </label>

          <div class="relative">
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              class="w-full p-3 pr-12 rounded-md bg-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan-400" />

            <!-- Eye Icon perfectly centered -->
            <button
              type="button"
              id="togglePassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-cyan-400 focus:outline-none">
              <i class="fa-regular fa-eye fa-lg"></i>
            </button>
          </div>
        </div>


        <!-- Login Button -->
        <button
          type="submit"
          class="w-full bg-cyan-400 text-black font-bold py-3 rounded-md hover:bg-cyan-600 transition">
          Login
        </button>
      </form>

      <!-- OR divider -->
      <div class="flex items-center justify-center my-6">
        <span class="text-gray-400 text-sm mx-2">or</span>
      </div>

      <!-- Login link -->
      <p class="text-center text-sm text-gray-400 mt-6">
        Do not have an account?
        <a href="signup.php" class="text-cyan-400 hover:underline">Sign Up</a>
      </p>
    </div>
  </div>
</body>
<script>
  const password = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  const icon = togglePassword.querySelector('i');

  togglePassword.addEventListener('click', () => {
    // Toggle type
    if (password.type === 'password') {
      password.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      password.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
</script>

</html>