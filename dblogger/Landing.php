<?php
// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user accepted cookies
$cookieAccepted = isset($_COOKIE['cookieAccepted']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/x-icon" href="./favicon.jpg">
  <title>D_BLOGGER - Your Creative Space</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fade-in-up {
      animation: fadeInUp 0.8s ease-out forwards;
    }

    .gradient-text {
      background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .glass {
      background: rgba(17, 24, 39, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(34, 211, 238, 0.2);
    }

    .glow {
      box-shadow: 0 0 20px rgba(34, 211, 238, 0.3);
    }

    .glow-hover:hover {
      box-shadow: 0 0 30px rgba(34, 211, 238, 0.5);
    }
  </style>
</head>

<body class="bg-gray-900 min-h-screen">
  <!-- Navigation -->
  <nav class="fixed w-full top-0 z-50 glass">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <div
            class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg flex items-center justify-center glow">
            <span class="text-gray-900 font-bold text-xl">D</span>
          </div>
          <span class="text-2xl font-bold text-cyan-400">D_<span class="text-2xl text-white font-bold">BLOGGER</span></span>
        </div>
        <div class="hidden md:flex items-center space-x-8">
          <a
            href="#features"
            class="text-gray-300 hover:text-cyan-400 transition">Features</a>
          <a
            href="#how-it-works"
            class="text-gray-300 hover:text-cyan-400 transition">How It Works</a>
          <a
            href="#testimonials"
            class="text-gray-300 hover:text-cyan-400 transition">Testimonials</a>
        </div>
        <div class="hidden md:flex items-center space-x-4">
          <button
            class="px-6 py-2 text-cyan-400 hover:text-cyan-300 font-medium transition"
            onclick="window.location.href='login.php'">
            Login
          </button>
          <button
            class="px-6 py-2 bg-gradient-to-r from-cyan-500 to-cyan-600 text-gray-900 rounded-lg hover:shadow-lg hover:shadow-cyan-500/50 transform hover:-translate-y-0.5 transition font-semibold"
            onclick="window.location.href='signup.php'">
            Sign Up
          </button>
        </div>
        <!-- Mobile menu button -->
        <button id="mobile-menu-btn" class="md:hidden text-cyan-400 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
      <!-- Mobile menu -->
      <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4">
        <div class="flex flex-col space-y-4">
          <a
            href="#features"
            class="text-gray-300 hover:text-cyan-400 transition">Features</a>
          <a
            href="#how-it-works"
            class="text-gray-300 hover:text-cyan-400 transition">How It Works</a>
          <a
            href="#testimonials"
            class="text-gray-300 hover:text-cyan-400 transition">Testimonials</a>
          <button
            class="px-6 py-2 text-cyan-400 hover:text-cyan-300 text-center font-medium transition text-left"
            onclick="window.location.href='login.php'">
            Login
          </button>
          <button
            class="px-6 py-2 bg-gradient-to-r from-cyan-500 to-cyan-600 text-gray-900 rounded-lg hover:shadow-lg hover:shadow-cyan-500/50 transform hover:-translate-y-0.5 transition font-semibold"
            onclick="window.location.href='signup.php'">
            Sign Up
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="pt-32 pb-20 px-6">
    <div class="max-w-7xl mx-auto">
      <div class="text-center fade-in-up">
        <h1 class="text-6xl md:text-7xl font-bold text-white mb-6">
          Share Your Stories,<br />
          <span class="gradient-text">Inspire the World</span>
        </h1>
        <p class="text-xl text-gray-400 mb-10 max-w-2xl mx-auto">
          Join thousands of writers who are sharing their ideas, experiences,
          and creativity on the platform built for storytellers.
        </p>
        <div
          class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <button
            class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-cyan-600 text-gray-900 rounded-lg text-lg font-semibold hover:shadow-2xl hover:shadow-cyan-500/50 transform hover:scale-105 transition"
            onclick="window.location.href='signup.php'">
            Start Writing Free
          </button>
          <button
            class="px-8 py-4 bg-gray-800 text-cyan-400 rounded-lg text-lg font-semibold border-2 border-cyan-500/30 hover:border-cyan-400 hover:bg-gray-700 transition"
            onclick="window.location.href='dashboard.php'">
            Explore Stories
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-20 px-6 bg-gray-800/50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Everything You Need to Succeed
        </h2>
        <p class="text-xl text-gray-400">
          Powerful tools designed for modern writers and content creators
        </p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <div
          class="p-8 rounded-2xl bg-gray-800 border border-cyan-500/20 hover:border-cyan-400/50 hover:shadow-xl hover:shadow-cyan-500/20 transition transform hover:-translate-y-2">
          <div
            class="w-14 h-14 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center mb-6 glow-hover">
            <svg
              class="w-8 h-8 text-gray-900"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-white mb-3">Intuitive Editor</h3>
          <p class="text-gray-400">
            Write with a distraction-free editor that supports markdown, rich
            formatting, and embedded media.
          </p>
        </div>
        <div
          class="p-8 rounded-2xl bg-gray-800 border border-cyan-500/20 hover:border-cyan-400/50 hover:shadow-xl hover:shadow-cyan-500/20 transition transform hover:-translate-y-2">
          <div
            class="w-14 h-14 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center mb-6 glow-hover">
            <svg
              class="w-8 h-8 text-gray-900"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-white mb-3">
            Build Your Audience
          </h3>
          <p class="text-gray-400">
            Grow your following with built-in SEO, social sharing, and email
            newsletter integration.
          </p>
        </div>
        <div
          class="p-8 rounded-2xl bg-gray-800 border border-cyan-500/20 hover:border-cyan-400/50 hover:shadow-xl hover:shadow-cyan-500/20 transition transform hover:-translate-y-2">
          <div
            class="w-14 h-14 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-xl flex items-center justify-center mb-6 glow-hover">
            <svg
              class="w-8 h-8 text-gray-900"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-white mb-3">
            Analytics & Insights
          </h3>
          <p class="text-gray-400">
            Track your performance with detailed analytics on views,
            engagement, and reader demographics.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section id="how-it-works" class="py-20 px-6 bg-gray-900">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Start Publishing in Minutes
        </h2>
        <p class="text-xl text-gray-400">
          Simple steps to share your voice with the world
        </p>
      </div>
      <div class="grid md:grid-cols-3 gap-12">
        <div class="text-center">
          <div
            class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-full flex items-center justify-center text-gray-900 text-2xl font-bold mx-auto mb-6 glow">
            1
          </div>
          <h3 class="text-xl font-bold text-white mb-3">
            Create Your Account
          </h3>
          <p class="text-gray-400">
            Sign up in seconds and customize your writer profile to reflect
            your unique voice.
          </p>
        </div>
        <div class="text-center">
          <div
            class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-full flex items-center justify-center text-gray-900 text-2xl font-bold mx-auto mb-6 glow">
            2
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Write Your Story</h3>
          <p class="text-gray-400">
            Use our powerful editor to craft compelling content with images,
            videos, and code.
          </p>
        </div>
        <div class="text-center">
          <div
            class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-full flex items-center justify-center text-gray-900 text-2xl font-bold mx-auto mb-6 glow">
            3
          </div>
          <h3 class="text-xl font-bold text-white mb-3">
            Reach Your Audience
          </h3>
          <p class="text-gray-400">
            Publish instantly and watch your story reach readers around the
            globe.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="testimonials" class="py-20 px-6 bg-gray-800/50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Loved by Writers Everywhere
        </h2>
        <p class="text-xl text-gray-400">See what our community has to say</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="p-8 rounded-2xl bg-gray-800 border border-gray-700">
          <div class="flex items-center mb-4">
            <img
              src="akka16.jpeg"
              alt="image1"
              class="w-12 h-12 rounded-full object-cover mr-4" />
            <div>
              <div class="font-bold text-white">Geethma Rathnayaka</div>
              <div class="text-gray-400 text-sm">Doctor</div>
            </div>
          </div>
          <p class="text-gray-300 italic">
            "D_BLOGGER transformed how I share my ideas. The editor is
            seamless, and I've grown my audience by 300% in just 6 months!"
          </p>
        </div>
        <div class="p-8 rounded-2xl bg-gray-800 border border-gray-700">
          <div class="flex items-center mb-4">
            <img
              src="IMG-20240302-WA0010.jpg"
              alt="image2"
              class="w-12 h-12 rounded-full object-cover mr-4" />
            <div>
              <div class="font-bold text-white">Dinith Rathnayaka</div>
              <div class="text-gray-400 text-sm">Software Developer</div>
            </div>
          </div>
          <p class="text-gray-300 italic">
            "Finally, a platform that understands writers. The analytics help
            me understand my readers, and the community is incredibly
            supportive."
          </p>
        </div>
        <div class="p-8 rounded-2xl bg-gray-800 border border-gray-700">
          <div class="flex items-center mb-4">
            <img
              src="WhatsApp Image 2025-10-22 at 14.49.36.jpeg"
              alt="image3"
              class="w-12 h-12 rounded-full object-cover mr-4" />
            <div>
              <div class="font-bold text-white">Nikini Muthugala</div>
              <div class="text-gray-400 text-sm">Fashion Designer</div>
            </div>
          </div>
          <p class="text-gray-300 italic">
            "I've tried many platforms, but D_BLOGGER is where my stories
            truly come to life. The reading experience is beautiful and
            engaging."
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section
    class="py-20 px-6 bg-gradient-to-r from-gray-900 via-cyan-900/20 to-gray-900 border-y border-cyan-500/20">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
        Ready to Start Your Journey?
      </h2>
      <p class="text-xl text-gray-300 mb-10">
        Join our community of passionate writers and start sharing your
        stories today. It's free to get started!
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button
          class="px-10 py-4 bg-gradient-to-r from-cyan-500 to-cyan-600 text-gray-900 rounded-lg text-lg font-semibold hover:shadow-2xl hover:shadow-cyan-500/50 transform hover:scale-105 transition"
          onclick="window.location.href='signup.php'">
          Create Free Account
        </button>
      </div>
    </div>
  </section>

  <!-- FAQ Section - D_BLOGGER -->
  <section
    id="faq"
    class="py-16 px-6 bg-gradient-to-r from-gray-900 via-cyan-900/20 to-gray-900 border-t border-cyan-500/10">
    <div class="max-w-4xl mx-auto text-white text-center">
      <h2 class="text-4xl md:text-5xl font-bold mb-4">
        Frequently Asked Questions
      </h2>
      <p class="text-gray-300 text-lg mb-8">
        Everything you need to know to get started with D_BLOGGER — fast,
        secure, and simple.
      </p>

      <div class="space-y-4" id="faq-accordion">
        <!-- single accordion item -->
        <div
          class="bg-gray-800/60 rounded-lg border border-cyan-500/10 overflow-hidden">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none focus:ring-2 focus:ring-cyan-400"
            aria-expanded="false"
            aria-controls="faq1"
            id="faq1-btn">
            <span class="text-lg font-medium">What is D_BLOGGER?</span>
            <svg
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            id="faq1"
            class="px-5 pb-4 hidden text-gray-300"
            role="region"
            aria-labelledby="faq1-btn">
            <p>
              D_BLOGGER is a simple blogging platform: sign up, go to the
              dashboard, create and edit posts, and read others' posts. It's
              designed to be easy but secure.
            </p>
          </div>
        </div>

        <div
          class="bg-gray-800/60 rounded-lg border border-cyan-500/10 overflow-hidden">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none focus:ring-2 focus:ring-cyan-400"
            aria-expanded="false"
            aria-controls="faq2"
            id="faq2-btn">
            <span class="text-lg font-medium">How do I create an account?</span>
            <svg
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            id="faq2"
            class="px-5 pb-4 hidden text-gray-300"
            role="region"
            aria-labelledby="faq2-btn">
            <p>
              Click "Create Free Account" on the landing page. Provide a valid
              email and strong password. You will receive an email
              verification link before you can publish posts.
            </p>
          </div>
        </div>

        <div
          class="bg-gray-800/60 rounded-lg border border-cyan-500/10 overflow-hidden">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none focus:ring-2 focus:ring-cyan-400"
            aria-expanded="false"
            aria-controls="faq3"
            id="faq3-btn">
            <span class="text-lg font-medium">Can I edit or delete my blog posts?</span>
            <svg
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            id="faq3"
            class="px-5 pb-4 hidden text-gray-300"
            role="region"
            aria-labelledby="faq3-btn">
            <p>
              Yes. From your dashboard you can create, edit, save drafts,
              publish, or delete posts. Posts you publish appear in the global
              feed for others to read.
            </p>
          </div>
        </div>

        <div
          class="bg-gray-800/60 rounded-lg border border-cyan-500/10 overflow-hidden">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none focus:ring-2 focus:ring-cyan-400"
            aria-expanded="false"
            aria-controls="faq4"
            id="faq4-btn">
            <span class="text-lg font-medium">Is my data safe?</span>
            <svg
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            id="faq4"
            class="px-5 pb-4 hidden text-gray-300"
            role="region"
            aria-labelledby="faq4-btn">
            <p>
              We follow best practices (hashed passwords, prepared statements,
              CSRF tokens). For production you must enable HTTPS, secure
              cookies, and proper server hardening. See security checklist
              below.
            </p>
          </div>
        </div>

        <div
          class="bg-gray-800/60 rounded-lg border border-cyan-500/10 overflow-hidden">
          <button
            class="w-full flex items-center justify-between px-5 py-4 text-left focus:outline-none focus:ring-2 focus:ring-cyan-400"
            aria-expanded="false"
            aria-controls="faq5"
            id="faq5-btn">
            <span class="text-lg font-medium">How do I report abuse or spam?</span>
            <svg
              class="w-5 h-5 transform transition-transform"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            id="faq5"
            class="px-5 pb-4 hidden text-gray-300"
            role="region"
            aria-labelledby="faq5-btn">
            <p>
              Each post has a report button. Reported content goes to admins
              for review. We also rate-limit suspicious actions and enable
              moderation tools in the admin panel.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });

    // Close mobile menu when clicking on links
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
      });
    });

    // Accessible accordion: toggle on click, keyboard-friendly (Enter/Space)
    document.querySelectorAll("#faq-accordion > div").forEach((item) => {
      const btn = item.querySelector("button");
      const panel = item.querySelector('[role="region"], div[id^="faq"]');

      const setExpanded = (expanded) => {
        btn.setAttribute("aria-expanded", expanded);
        if (expanded) {
          panel.classList.remove("hidden");
          btn.querySelector("svg").classList.add("rotate-180");
        } else {
          panel.classList.add("hidden");
          btn.querySelector("svg").classList.remove("rotate-180");
        }
      };

      btn.addEventListener("click", () => {
        const isExpanded = btn.getAttribute("aria-expanded") === "true";
        // optional: collapse others for single-open behavior:
        document
          .querySelectorAll("#faq-accordion button")
          .forEach((b) => setExpanded.call(null, false));
        setExpanded(!isExpanded);
      });

      btn.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          btn.click();
        }
      });
    });
  </script>

  <!-- Footer -->
  <footer class="bg-black text-gray-400 py-12 px-6 border-t border-gray-800">
    <div class="max-w-7xl mx-auto">
      <div class="grid md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center space-x-2 mb-4">
            <div
              class="w-8 h-8 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-lg flex items-center justify-center">
              <span class="text-gray-900 font-bold">D</span>
            </div>
            <span class="text-xl font-bold text-cyan-400">D_<span class="text-xl text-white font-bold">BLOGGER</span></span>
          </div>
          <p class="text-gray-500">
            Empowering writers to share their stories with the world.
          </p>
        </div>
        <div>
          <h4 class="text-white font-bold mb-4">Product</h4>
          <ul class="space-y-2">
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Features</a>
            </li>
            <li>
              <a href="#" class="hover:text-cyan-400 transition">FAQ</a>
            </li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-bold mb-4">Company</h4>
          <ul class="space-y-2">
            <li>
              <a href="#" class="hover:text-cyan-400 transition">About</a>
            </li>
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Blog</a>
            </li>
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Careers</a>
            </li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-bold mb-4">Legal</h4>
          <ul class="space-y-2">
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Privacy</a>
            </li>
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Terms</a>
            </li>
            <li>
              <a href="#" class="hover:text-cyan-400 transition">Contact</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="border-t border-gray-800 pt-8 text-center text-gray-500">
        <p>&copy; 2025 D_BLOGGER. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script>
    // Smooth scrolling
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute("href"));
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
            block: "start"
          });
        }
      });
    });

    // Add scroll effect to navbar
    window.addEventListener("scroll", () => {
      const nav = document.querySelector("nav");
      if (window.scrollY > 50) {
        nav.classList.add("shadow-lg");
        nav.classList.add("shadow-cyan-500/20");
      } else {
        nav.classList.remove("shadow-lg");
        nav.classList.remove("shadow-cyan-500/20");
      }
    });
  </script>

 <!-- Cookie Popup (only if not accepted) -->
  <?php if (!$cookieAccepted): ?>
  <div id="cookie-popup"
    class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-gray-900 text-gray-100 px-6 py-4 rounded-xl shadow-lg w-11/12 max-w-md border border-cyan-500 z-50 transition-all duration-500 opacity-0 translate-y-10">

    <!-- Header Row (Message + Close Button) -->
    <div class="flex justify-between items-start">
      <p class="text-sm leading-relaxed pr-3">
        🍪 We use cookies to improve your experience. By continuing, you agree to our
        <a href="privacy_policy.php" class="text-cyan-400 underline hover:text-cyan-300">Privacy Policy</a>.
      </p>

      <!-- Close Button -->
      <button id="close-popup"
        class="text-gray-400 hover:text-cyan-400 transition text-lg ml-2 focus:outline-none">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Accept Button -->
    <div class="flex justify-center mt-4">
      <button id="accept-cookies"
        class="bg-cyan-500 text-black font-semibold px-4 py-2 rounded-md hover:bg-cyan-600 transition">
        Accept
      </button>
    </div>
  </div>
  <?php endif; ?>

  <script>
    const popup = document.getElementById('cookie-popup');

    if (popup) {
      window.addEventListener('load', () => {
        // Fade-in popup
        setTimeout(() => {
          popup.classList.remove('opacity-0', 'translate-y-10');
          popup.classList.add('opacity-100', 'translate-y-0');
        }, 300);

        //Accept Button
        document.getElementById('accept-cookies').addEventListener('click', () => {
          fetch('set_cookie.php')
            .then(() => {
              popup.classList.add('opacity-0', 'translate-y-10');
              setTimeout(() => popup.remove(), 500);
            });
        });

        //Close Button
        document.getElementById('close-popup').addEventListener('click', () => {
          popup.classList.add('opacity-0', 'translate-y-10');
          setTimeout(() => popup.remove(), 500);
        });
      });
    }
  </script>

</body>

</html>