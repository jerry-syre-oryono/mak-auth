<?php
/**
 * Student Enrollment Validator - Main Application
 * 
 * Multi-step enrollment verification system with email token delivery
 */

require_once __DIR__ . '/includes/config.php';

$pageTitle = 'MAK-AUTH';
include __DIR__ . '/includes/header.php';
?>

<!-- ============================================== -->
<!-- GSAP + ScrollTrigger + TextPlugin CDN          -->
<!-- ============================================== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/TextPlugin.min.js"></script>

<!-- ============================================== -->
<!-- GSAP Animations Custom Styles (Non-Destructive)-->
<!-- ============================================== -->
<style>
  /* Full Screen Loader */
  #mak-gsap-loader {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background-color: #030c08;
    background-image: radial-gradient(circle at center, #051a13 0%, #030c08 100%);
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    will-change: opacity;
  }
  
  #loader-padlock-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    display: flex;
    justify-content: center;
    align-items: center;
    will-change: transform, opacity;
  }

  #loader-glow {
    position: absolute;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, transparent 65%);
    filter: blur(12px);
    will-change: transform, opacity;
  }

  #loader-svg {
    position: relative;
    z-index: 2;
    width: 80px;
    height: 80px;
    filter: drop-shadow(0 0 10px rgba(16,185,129,0.5));
  }

  /* Safe Cursor Glow */
  #gsap-cursor-glow {
    position: fixed;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,0.06) 0%, transparent 70%);
    pointer-events: none;
    transform: translate(-50%, -50%);
    z-index: 1;
    will-change: transform;
  }
</style>

<!-- ============================================== -->
<!-- Full-Screen Loader Element                     -->
<!-- ============================================== -->
<div id="mak-gsap-loader">
  <div id="loader-padlock-wrapper">
    <div id="loader-glow"></div>
    <svg id="loader-svg" viewBox="0 0 100 120" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- Shackle Pivot at right side -->
      <g id="shackle-hinge" style="transform-origin: 70px 45px;">
        <path d="M30 45 V30 C30 10 70 10 70 30 V45" stroke="#10b981" stroke-width="8" stroke-linecap="round" fill="none"/>
      </g>
      <!-- Lock Body -->
      <rect x="15" y="45" width="70" height="55" rx="12" fill="#064e3b" stroke="#10b981" stroke-width="4"/>
      <!-- Keyhole -->
      <circle cx="50" cy="65" r="8" fill="#10b981"/>
      <path d="M46 68 L44 82 H56 L54 68 Z" fill="#10b981"/>
    </svg>
  </div>
</div>

<!-- ============================================== -->
<!-- Safe Cursor Glow                               -->
<!-- ============================================== -->
<div id="gsap-cursor-glow"></div>

    <!-- Glass Panel Card -->
    <div class="glass-panel w-full max-w-lg md:max-w-3xl p-6 md:p-8 relative z-10">
      
      <!-- Header with MAK style -->
      <div class="flex flex-col items-center text-center gap-3 mb-6">
        <img src="Mak-Logo.png" alt="Mak crest" class="w-20 h-20 md:w-24 md:h-24 object-contain" onerror="this.style.display='none'">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
            MAK-<span class="text-emerald-700">AUTH</span>
          </h1>
          <p class="text-xs uppercase tracking-wider text-gray-600 mt-1">
            Secure Student Webmail Setup
          </p>
        </div>
      </div>

      <div class="mt-7 mb-3 p-4 bg-white/70 border border-emerald-100 rounded-lg text-sm text-gray-800">
        <p class="m-0 font-medium">Enter a valid Student and Registration number combination below to get started!</p>
      </div>

      <!-- Step 1: Verification Form -->
      <form id="step1Form" class="space-y-4">
        <div class="md:flex md:gap-4 md:items-end">
          <div class="md:w-1/2">
            <label for="studentIdInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
              <i class="fa-regular fa-id-card text-emerald-600"></i>
              Student ID
            </label>
            <input 
              type="text" 
              id="studentIdInput" 
              name="studentId"
              class="screenshot-input green-focus-ring"
              placeholder="e.g., 210009190"
              required
            />
          </div>

          <div class="md:w-1/2">
            <label for="regNumInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
              <i class="fa-regular fa-file-lines text-emerald-600"></i>
              Registration Number
            </label>
            <input 
              type="text" 
              id="regNumInput" 
              name="registrationNumber"
              class="screenshot-input green-focus-ring"
              placeholder="e.g., 21/U/9190"
              required
            />
          </div>
        </div>

        <div class="relative group mt-4">
          <button 
            type="submit" 
            id="submitStep1Btn"
            class="btn-3d w-full bg-gradient-to-b from-[#10b981] to-[#059669] text-white text-lg font-bold py-4 rounded-2xl border border-[#10b981]/40 tracking-widest flex items-center justify-center gap-3 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span id="btnTextStep1">Submit Details</span>
            <svg id="loadingSpinnerStep1" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <i class="fa-solid fa-arrow-right text-white text-xl group-hover:translate-x-1 transition-transform"></i>
          </button>
        </div>
      </form>

      <!-- Step 2: Details & Alternative Email Form -->
      <div id="step2Container" class="hidden space-y-6 mt-4 card-enter">
        <div class="success-card p-4">
          <div class="flex items-center gap-2 mb-3">
            <i class="fa-regular fa-circle-check text-emerald-600 text-lg"></i>
            <h3 class="font-semibold text-emerald-800">Student Record Found</h3>
          </div>
          <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Name:</strong> <span id="displayFullName"></span></p>
            <p><strong>Program:</strong> <span id="displayProgram"></span></p>
            <p class="mt-3 pt-3 border-t border-emerald-200">
              <strong>University Webmail:</strong><br/>
              <span id="displayWebmail" class="font-mono text-emerald-700 font-semibold mt-1 inline-block bg-white/60 px-2 py-1 rounded"></span>
            </p>
          </div>
        </div>

        <form id="step2Form" class="space-y-4">
          <!-- Hidden fields to store student data for form submission -->
          <input type="hidden" id="hiddenStudentId" name="studentId">
          <input type="hidden" id="hiddenRegistrationNumber" name="registrationNumber">
          <input type="hidden" id="hiddenStudentName" name="studentName">
          <input type="hidden" id="hiddenWebmail" name="webmail">
          
          <!-- Initial Section: Alternative Email Input -->
          <div id="emailInputSection">
            <label for="altEmailInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
              <i class="fa-regular fa-envelope-open text-emerald-600"></i>
              Alternative Email for OTP Code
            </label>
            <input 
              type="email" 
              id="altEmailInput" 
              name="altEmail"
              class="screenshot-input green-focus-ring"
              placeholder="e.g., personal@gmail.com"
              required
            />
            <p class="text-xs text-gray-500 mt-1.5 ml-1">
              <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
              We'll send your secure OTP Code to this address
            </p>
          </div>

          <!-- SEND TOKEN Button Section -->
          <div id="sendTokenButtonSection" class="relative group">
            <button 
              type="button" 
              id="submitStep2Btn"
              class="btn-3d w-full bg-gradient-to-b from-[#10b981] to-[#059669] text-white text-lg font-bold py-4 rounded-2xl border border-[#10b981]/40 tracking-widest flex items-center justify-center gap-3 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span id="btnTextStep2">SEND OTP</span>
              <svg id="loadingSpinnerStep2" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
              <i class="fa-regular fa-paper-plane text-white text-xl group-hover:translate-x-1 transition-transform"></i>
            </button>
          </div>

          <!-- NEW: Token Input Section (hidden initially) -->
          <div id="tokenInputSection" class="hidden space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800">
              <i class="fa-solid fa-circle-info text-blue-600 mr-2"></i>
              <strong>OTP Received:</strong> Enter the OTP Code from your email to reveal your webmail credentials.
            </div>
            
            <div>
              <label for="otpCodeInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
                <i class="fa-solid fa-lock text-emerald-600"></i>
                Enter OTP Code
              </label>
              <input 
                type="text" 
                id="otpCodeInput" 
                name="otpCode"
                class="screenshot-input green-focus-ring text-center tracking-widest text-lg font-mono uppercase"
                placeholder="e.g. aB3X9k"
                maxlength="6"
                required
              />
              <p class="text-xs text-gray-500 mt-1.5 ml-1">
                <i class="fa-solid fa-lock-open text-emerald-600 mr-1"></i>
                This OTP expires in 10 minutes
              </p>
            </div>
            
            <!-- Resend OTP Button -->
            <div class="flex justify-between items-center px-1 pt-2">
              <p class="text-xs text-gray-500">
                Didn't receive the code? 
              </p>
              <button 
                type="button" 
                id="resendOtpBtn"
                class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i id="resendIcon" class="fa-solid fa-rotate-right"></i>
                <span id="resendText">Resend OTP</span>
              </button>
            </div>
          </div>

          <!-- NEW: Verify Token Button Section (hidden initially) -->
          <div id="verifyTokenButtonSection" class="hidden relative group">
            <button 
              type="button" 
              id="submitVerifyBtn"
              class="btn-3d w-full bg-gradient-to-b from-[#10b981] to-[#059669] text-white text-lg font-bold py-4 rounded-2xl border border-[#10b981]/40 tracking-widest flex items-center justify-center gap-3 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span id="btnTextVerify">VERIFY OTP</span>
              <svg id="loadingSpinnerVerify" class="animate-spin h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
              <i class="fa-solid fa-check text-white text-xl group-hover:translate-x-1 transition-transform"></i>
            </button>
          </div>

          <!-- NEW: Create Webmail Section (hidden initially) -->
          <div id="createWebmailSection" class="hidden space-y-4 card-enter">
            <div class="success-card p-4">
              <div class="flex items-center gap-2 mb-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <h3 class="font-semibold text-emerald-800">OTP Verified!</h3>
              </div>
              <p class="text-sm text-emerald-700">
                Please set your webmail password and confirm your alternative email to complete the process.
              </p>
            </div>

            <div class="bg-white/80 border border-emerald-100 rounded-lg p-4 space-y-4">
              <div>
                <label for="newPasswordInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
                  <i class="fa-solid fa-key text-emerald-600"></i>
                  New Webmail Password
                </label>
                <div class="relative mb-2">
                  <input 
                    type="password" 
                    id="newPasswordInput" 
                    class="screenshot-input green-focus-ring pr-12"
                    placeholder="Enter secure password"
                    required
                  />
                  <button 
                    type="button"
                    id="toggleNewPasswordBtn"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-600 transition-colors focus:outline-none p-2 z-20"
                    title="Toggle password visibility"
                  >
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
                
                <!-- NEW: Strength Indicator & Helper Text -->
                <div class="px-1 mt-2 mb-2">
                  <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold text-gray-600">Password Strength:</span>
                    <span id="strengthText" class="text-xs font-bold text-gray-400">None</span>
                  </div>
                  <div class="h-1.5 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div id="strengthBar" class="h-full bg-red-500 transition-all duration-300" style="width: 0%"></div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    <i class="fa-solid fa-circle-info text-emerald-600 mr-1"></i>
                    Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
                  </p>
                </div>
              </div>

              <div>
                <label for="confirmPasswordInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
                  <i class="fa-solid fa-key text-emerald-600"></i>
                  Repeat Password for Verification
                </label>
                <div class="relative">
                  <input 
                    type="password" 
                    id="confirmPasswordInput" 
                    class="screenshot-input green-focus-ring pr-12"
                    placeholder="Repeat password"
                    required
                  />
                  <button 
                    type="button"
                    id="toggleConfirmPasswordBtn"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-600 transition-colors focus:outline-none p-2 z-20"
                    title="Toggle password visibility"
                  >
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
              </div>

              <div>
                <label for="editAltEmailInput" class="block text-sm font-medium text-gray-700 mb-1.5 ml-1 flex items-center gap-2">
                  <i class="fa-solid fa-envelope text-emerald-600"></i>
                  Edit Alternative Email
                </label>
                <input 
                  type="email" 
                  id="editAltEmailInput" 
                  class="screenshot-input green-focus-ring"
                  placeholder="your@email.com"
                  required
                />
              </div>

              <button 
                type="button" 
                id="submitCreateWebmailBtn"
                class="btn-3d w-full bg-gradient-to-b from-[#10b981] to-[#059669] text-white text-lg font-bold py-4 rounded-2xl border border-[#10b981]/40 tracking-widest flex items-center justify-center gap-3 transition-all"
              >
                <span>CREATE WEBMAIL</span>
                <i class="fa-solid fa-plus text-white text-xl"></i>
              </button>
            </div>
          </div>

          <!-- NEW: Credentials Display Section (hidden initially) -->
          <div id="credentialsSection" class="hidden space-y-4 card-enter">
            <div class="success-card p-4 border-l-4 border-emerald-500 shadow-sm">
              <div class="flex items-center gap-2 mb-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <h3 class="font-semibold text-emerald-800">Account Created Successfully!</h3>
              </div>
              
              <!-- IMPORTANT USER INSTRUCTION -->
              <div class="bg-red-50 border border-red-200 rounded p-3 mb-4 mt-1">
                <p class="text-sm text-red-700">
                  <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                  <strong>IMPORTANT:</strong> This is the only time your credentials will be shown. Please save them securely. You will not be able to view this password again.
                </p>
              </div>

            <div class="bg-white/80 border-l-4 border-emerald-600 rounded-lg p-4 space-y-3">
              <div>
                <label class="block text-xs uppercase font-semibold text-gray-600 tracking-wide mb-1">
                  <i class="fa-regular fa-envelope text-emerald-600 mr-1"></i>
                  Webmail Address
                </label>
                <input 
                  type="text"
                  id="credWebmail"
                  class="w-full bg-gray-50 border border-gray-300 rounded px-3 py-2 font-mono text-emerald-700 font-semibold"
                  readonly
                />
              </div>

              <div>
                <label class="block text-xs uppercase font-semibold text-gray-600 tracking-wide mb-1">
                  <i class="fa-solid fa-key text-emerald-600 mr-1"></i>
                  Webmail Password
                </label>
                <div class="flex gap-2">
                  <input 
                    type="password"
                    id="credPassword"
                    class="flex-1 bg-gray-50 border border-gray-300 rounded px-3 py-2 font-mono text-emerald-700 font-semibold"
                    readonly
                  />
                  <button 
                    type="button"
                    id="togglePasswordBtn"
                    class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 px-3 py-2 rounded transition-colors"
                    title="Toggle password visibility"
                  >
                    <i class="fa-regular fa-eye"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Step 3: Token Sent Success -->
      <div id="step3Container" class="hidden space-y-6 mt-4 card-enter">
        <div class="success-card p-6 text-center">
          <i class="fa-solid fa-envelope-circle-check text-5xl text-emerald-600 mb-4 inline-block"></i>
          <h3 class="text-xl font-bold text-emerald-800 mb-2">OTP Sent!</h3>
          <p class="text-sm text-emerald-700 mb-4">
            A secure OTP Code has been sent to your alternative email.
          </p>
          <button onclick="location.reload()" class="text-sm font-semibold bg-white text-emerald-700 px-4 py-2 rounded-full border border-emerald-200 hover:bg-emerald-50 transition-colors">
            Start Over
          </button>
        </div>
      </div>

      <!-- Error Card (reused) -->
      <div id="errorCard" class="hidden mt-6 error-card p-4 card-enter">
        <div class="flex items-center gap-2 mb-2">
          <i class="fa-regular fa-circle-xmark text-red-600 text-lg"></i>
          <h3 class="font-semibold text-red-800">Error</h3>
        </div>
        <p id="errorMessage" class="text-sm text-red-700"></p>
      </div>

      <!-- Footer -->
      <div class="mt-8 pt-4 border-t border-gray-200 text-center">
        <p class="text-xs text-gray-500 italic">
          <i class="fa-regular fa-building mr-1"></i>
          MAK-AUTH • Secure  Verification System 
        </p>
      </div>
    </div>

  <!-- Form handling and interactions -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // DOM Elements
      const step1Form = document.getElementById('step1Form');
      const step2Container = document.getElementById('step2Container');
      const step2Form = document.getElementById('step2Form');
      const step3Container = document.getElementById('step3Container');
      const errorCard = document.getElementById('errorCard');
      const errorMessage = document.getElementById('errorMessage');
      const toast = document.getElementById('toast');
      const toastMessage = document.getElementById('toastMessage');

      const submitStep1Btn = document.getElementById('submitStep1Btn');
      const btnTextStep1 = document.getElementById('btnTextStep1');
      const loadingSpinnerStep1 = document.getElementById('loadingSpinnerStep1');

      const submitStep2Btn = document.getElementById('submitStep2Btn');
      const btnTextStep2 = document.getElementById('btnTextStep2');
      const loadingSpinnerStep2 = document.getElementById('loadingSpinnerStep2');

      const studentIdInput = document.getElementById('studentIdInput');
      const regNumInput = document.getElementById('regNumInput');
      const altEmailInput = document.getElementById('altEmailInput');
      const otpCodeInput = document.getElementById('otpCodeInput');
      const newPasswordInput = document.getElementById('newPasswordInput');
      const confirmPasswordInput = document.getElementById('confirmPasswordInput');
      const editAltEmailInput = document.getElementById('editAltEmailInput');

      const submitVerifyBtn = document.getElementById('submitVerifyBtn');
      const btnTextVerify = document.getElementById('btnTextVerify');
      const loadingSpinnerVerify = document.getElementById('loadingSpinnerVerify');
      
      const resendOtpBtn = document.getElementById('resendOtpBtn');
      const resendText = document.getElementById('resendText');
      const resendIcon = document.getElementById('resendIcon');
      let cooldownTimer = null;
      let cooldownSeconds = 0;
      
      const createWebmailSection = document.getElementById('createWebmailSection');
      const submitCreateWebmailBtn = document.getElementById('submitCreateWebmailBtn');
      const credentialsSection = document.getElementById('credentialsSection');

      const toggleNewPasswordBtn = document.getElementById('toggleNewPasswordBtn');
      const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPasswordBtn');
      const togglePasswordBtn = document.getElementById('togglePasswordBtn');
      const credPassword = document.getElementById('credPassword');

      // Display elements
      const displayFullName = document.getElementById('displayFullName');
      const displayProgram = document.getElementById('displayProgram');
      const displayWebmail = document.getElementById('displayWebmail');

      // Hidden fields for step 2
      const hiddenStudentId = document.getElementById('hiddenStudentId');
      const hiddenRegistrationNumber = document.getElementById('hiddenRegistrationNumber');
      const hiddenStudentName = document.getElementById('hiddenStudentName');
      const hiddenWebmail = document.getElementById('hiddenWebmail');

      // Current student data
      let currentStudent = null;

      // Show toast message
      function showToast(message) {
        if (!toastMessage || !toast) return;
        toastMessage.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(() => {
          toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
      }

      // Show error
      function showError(message) {
        if (!errorMessage || !errorCard) return;
        errorMessage.textContent = message;
        errorCard.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      // Hide error
      function hideError() {
        if (errorCard) errorCard.classList.add('hidden');
      }

      // Set loading state for step 1
      function setStep1Loading(loading) {
        if (!submitStep1Btn) return;
        submitStep1Btn.disabled = loading;
        if (btnTextStep1) btnTextStep1.textContent = loading ? 'Verifying...' : 'Submit Details';
        if (loadingSpinnerStep1) loadingSpinnerStep1.classList.toggle('hidden', !loading);
      }

      // Set loading state for step 2
      function setStep2Loading(loading) {
        if (!submitStep2Btn) return;
        submitStep2Btn.disabled = loading;
        if (btnTextStep2) btnTextStep2.textContent = loading ? 'Sending...' : 'SEND TOKEN';
        if (loadingSpinnerStep2) loadingSpinnerStep2.classList.toggle('hidden', !loading);
      }

      // Step 1: Form submit - Validate student
      if (step1Form) {
        step1Form.addEventListener('submit', async (e) => {
          e.preventDefault();
          hideError();
          setStep1Loading(true);

          const studentId = studentIdInput.value.trim();
          const registrationNumber = regNumInput.value.trim();

          // Basic client-side validation
          if (!studentId || !registrationNumber) {
            showError('Please fill in all fields.');
            setStep1Loading(false);
            return;
          }

          try {
            const formData = new FormData();
            formData.append('studentId', studentId);
            formData.append('registrationNumber', registrationNumber);

            const response = await fetch('process/validate_student.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || `Server responded with status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              // Store student data
              currentStudent = data.student;

              // Populate hidden fields for step 2
              if (hiddenStudentId) hiddenStudentId.value = currentStudent.studentId;
              if (hiddenRegistrationNumber) hiddenRegistrationNumber.value = currentStudent.registrationNumber;
              if (hiddenStudentName) hiddenStudentName.value = currentStudent.fullName;
              if (hiddenWebmail) hiddenWebmail.value = currentStudent.webmail;

              // Display student information
              if (displayFullName) displayFullName.textContent = currentStudent.fullName;
              if (displayProgram) displayProgram.textContent = currentStudent.program;
              if (displayWebmail) displayWebmail.textContent = currentStudent.webmail;

              // Show step 2
              step1Form.classList.add('hidden');
              if (step2Container) step2Container.classList.remove('hidden');
              hideError();
              showToast('Student verified! Please provide your alternative email.');
            } else {
              showError(data.message || 'Verification failed. Please try again.');
            }
          } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'An unexpected error occurred. Please try again.');
          } finally {
            setStep1Loading(false);
          }
        });
      }

      // Step 2: Send token button click - Send token email
      if (submitStep2Btn) {
        submitStep2Btn.addEventListener('click', async (e) => {
          e.preventDefault();
          hideError();
          setStep2Loading(true);

          const altEmail = altEmailInput.value.trim();

          // Basic validation
          if (!altEmail) {
            showError('Please enter your alternative email.');
            setStep2Loading(false);
            return;
          }

          // Email format validation
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(altEmail)) {
            showError('Please enter a valid email address.');
            setStep2Loading(false);
            return;
          }

          try {
            const formData = new FormData(step2Form);

            const response = await fetch('process/send_token.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || `Server responded with status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              // Hide email input section and show token input section
              const emailInputSection = document.getElementById('emailInputSection');
              const sendTokenButtonSection = document.getElementById('sendTokenButtonSection');
              const tokenInputSection = document.getElementById('tokenInputSection');
              const verifyTokenButtonSection = document.getElementById('verifyTokenButtonSection');

              if (emailInputSection) emailInputSection.classList.add('hidden');
              if (sendTokenButtonSection) sendTokenButtonSection.classList.add('hidden');
              if (tokenInputSection) tokenInputSection.classList.remove('hidden');
              if (verifyTokenButtonSection) verifyTokenButtonSection.classList.remove('hidden');
              
              showToast('OTP sent! Check your email and enter it below.');
              startResendCooldown(60); // Start cooldown after initial send
            } else {
              showError(data.message || 'Failed to send OTP. Please try again.');
            }
          } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'An unexpected error occurred. Please try again.');
          } finally {
            setStep2Loading(false);
          }
        });
      }

      // NEW: Resend OTP Logic
      function startResendCooldown(seconds) {
        cooldownSeconds = seconds;
        resendOtpBtn.disabled = true;
        if (resendIcon) resendIcon.classList.add('fa-spin');
        
        updateResendUI();
        
        if (cooldownTimer) clearInterval(cooldownTimer);
        
        cooldownTimer = setInterval(() => {
          cooldownSeconds--;
          if (cooldownSeconds <= 0) {
            clearInterval(cooldownTimer);
            resendOtpBtn.disabled = false;
            if (resendIcon) resendIcon.classList.remove('fa-spin');
            resendText.textContent = 'Resend OTP';
          } else {
            updateResendUI();
          }
        }, 1000);
      }

      function updateResendUI() {
        resendText.textContent = `Resend in ${cooldownSeconds}s`;
      }

      if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          if (cooldownSeconds > 0) return;
          
          hideError();
          resendOtpBtn.disabled = true;
          const originalText = resendText.textContent;
          resendText.textContent = 'Sending...';

          try {
            const formData = new FormData(step2Form);

            const response = await fetch('process/send_token.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || `Server responded with status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              showToast('A new OTP has been sent to your email.');
              startResendCooldown(60); // 60 seconds cooldown
            } else {
              showError(data.message || 'Failed to resend OTP. Please try again.');
              resendOtpBtn.disabled = false;
              resendText.textContent = originalText;
            }
          } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'An unexpected error occurred. Please try again.');
            resendOtpBtn.disabled = false;
            resendText.textContent = originalText;
          }
        });
      }

      // NEW: Verify token - Submit
      function setVerifyLoading(loading) {
        if (!submitVerifyBtn) return;
        submitVerifyBtn.disabled = loading;
        if (btnTextVerify) btnTextVerify.textContent = loading ? 'Verifying...' : 'VERIFY TOKEN';
        if (loadingSpinnerVerify) loadingSpinnerVerify.classList.toggle('hidden', !loading);
      }

      if (submitVerifyBtn) {
        submitVerifyBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          hideError();
          setVerifyLoading(true);

          const otpCode = otpCodeInput.value.trim();

          // Validation
          if (!otpCode) {
            showError('Please enter the OTP Code.');
            setVerifyLoading(false);
            return;
          }

          try {
            const formData = new FormData();
            formData.append('otpCode', otpCode);

            const response = await fetch('process/verify_token.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || `Server responded with status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              // Hide token input section and show create webmail section
              const tokenInputSection = document.getElementById('tokenInputSection');
              const verifyTokenButtonSection = document.getElementById('verifyTokenButtonSection');

              if (tokenInputSection) tokenInputSection.classList.add('hidden');
              if (verifyTokenButtonSection) verifyTokenButtonSection.classList.add('hidden');
              if (createWebmailSection) createWebmailSection.classList.remove('hidden');
              
              // Pre-fill alt email
              if (editAltEmailInput && altEmailInput) editAltEmailInput.value = altEmailInput.value;
              
              // Store portal URL and webmail for later
              if (currentStudent) currentStudent.portalUrl = data.credentials.portalUrl;
              
              showToast('OTP verified! Please set your password.');
            } else {
              showError(data.message || 'OTP verification failed. Please try again.');
            }
          } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'An unexpected error occurred. Please try again.');
          } finally {
            setVerifyLoading(false);
          }
        });
      }

      // Password Strength Logic
      const strengthBar = document.getElementById('strengthBar');
      const strengthText = document.getElementById('strengthText');
      const ruleLength = /.{8,}/;
      const ruleUpper = /[A-Z]/;
      const ruleLower = /[a-z]/;
      const ruleNumber = /[0-9]/;
      const ruleSpecial = /[!@#$%^&*()[\]{}|\\;:'",.<>/?`~_\-=+]/;

      if (newPasswordInput && strengthBar && strengthText) {
        newPasswordInput.addEventListener('input', (e) => {
          const pwd = e.target.value;
          let score = 0;

          if (ruleUpper.test(pwd)) score++;
          if (ruleLower.test(pwd)) score++;
          if (ruleNumber.test(pwd)) score++;
          if (ruleSpecial.test(pwd)) score++;
          if (ruleLength.test(pwd)) score++;

          let percentage = '33%';
          let color = 'bg-red-500';
          let text = 'Weak';
          let textColor = 'text-red-500';

          if (pwd.length === 0) {
            percentage = '0%';
            text = 'None';
            textColor = 'text-gray-400';
          } else if (score === 5) {
            percentage = '100%';
            color = 'bg-emerald-500';
            text = 'Strong';
            textColor = 'text-emerald-500';
          } else if (score >= 3 && pwd.length >= 6) {
            percentage = '66%';
            color = 'bg-orange-400';
            text = 'Medium';
            textColor = 'text-orange-500';
          }

          strengthBar.className = `h-full transition-all duration-300 ${color}`;
          strengthBar.style.width = percentage;
          strengthText.textContent = text;
          strengthText.className = `text-xs font-bold ${textColor}`;
        });
      }

      // NEW: Create Webmail button handler
      if (submitCreateWebmailBtn) {
        submitCreateWebmailBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          hideError();

          const password = newPasswordInput.value;
          const confirmPassword = confirmPasswordInput.value;
          const altEmail = editAltEmailInput.value.trim();

          // Validation
          if (!password || !confirmPassword || !altEmail) {
            showError('Please fill in all fields.');
            return;
          }

          if (password !== confirmPassword) {
            showError('Passwords do not match. Please try again.');
            return;
          }

          // Full regex test on frontend
          if (password.length < 8 || !ruleUpper.test(password) || !ruleLower.test(password) || !ruleNumber.test(password) || !ruleSpecial.test(password)) {
            showError('Password does not meet security requirements. Need 8+ characters, uppercase, lowercase, number, and special character.');
            return;
          }

          try {
            submitCreateWebmailBtn.disabled = true;
            submitCreateWebmailBtn.innerHTML = '<span>CREATING...</span><svg class="animate-spin ml-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>';
            
            const formData = new FormData();
            formData.append('password', password);
            formData.append('altEmail', altEmail);

            const response = await fetch('process/create_webmail.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              const errorData = await response.json().catch(() => ({}));
              throw new Error(errorData.message || `Server responded with status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              // Success - show final credentials
              if (createWebmailSection) createWebmailSection.classList.add('hidden');
              if (credentialsSection) credentialsSection.classList.remove('hidden');
              
              if (currentStudent) {
                const credWebmail = document.getElementById('credWebmail');
                const credPassword = document.getElementById('credPassword');
                
                if (credWebmail) credWebmail.value = currentStudent.webmail;
                if (credPassword) credPassword.value = password;
              }
              
              showToast('Webmail created successfully!');
            } else {
              showError(data.message || 'Webmail creation failed. Please try again.');
            }
          } catch (error) {
            console.error('Error:', error);
            showError(error.message || 'An unexpected error occurred. Please try again.');
          } finally {
            submitCreateWebmailBtn.disabled = false;
            submitCreateWebmailBtn.innerHTML = '<span>CREATE WEBMAIL</span><i class="fa-solid fa-plus text-white text-xl ml-2"></i>';
          }
        });
      }

      // NEW: Toggle password visibility
      let passwordVisible = false;

      if (togglePasswordBtn && credPassword) {
        togglePasswordBtn.addEventListener('click', (e) => {
          e.preventDefault();
          passwordVisible = !passwordVisible;
          
          if (passwordVisible) {
            credPassword.type = 'text';
            togglePasswordBtn.innerHTML = '<i class="fa-regular fa-eye-slash"></i>';
          } else {
            credPassword.type = 'password';
            togglePasswordBtn.innerHTML = '<i class="fa-regular fa-eye"></i>';
          }
        });
      }

      // NEW: Toggle New Password Visibility
      let newPasswordVisible = false;

      if (toggleNewPasswordBtn && newPasswordInput) {
        toggleNewPasswordBtn.addEventListener('click', (e) => {
          e.preventDefault();
          newPasswordVisible = !newPasswordVisible;
          newPasswordInput.type = newPasswordVisible ? 'text' : 'password';
          toggleNewPasswordBtn.innerHTML = newPasswordVisible ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
      }

      // NEW: Toggle Confirm Password Visibility
      let confirmPasswordVisible = false;

      if (toggleConfirmPasswordBtn && confirmPasswordInput) {
        toggleConfirmPasswordBtn.addEventListener('click', (e) => {
          e.preventDefault();
          confirmPasswordVisible = !confirmPasswordVisible;
          confirmPasswordInput.type = confirmPasswordVisible ? 'text' : 'password';
          toggleConfirmPasswordBtn.innerHTML = confirmPasswordVisible ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
      }

      // Auto-focus on page load
      if (studentIdInput) studentIdInput.focus();
    });
  </script>

  <!-- ============================================== -->
  <!-- GSAP Motion & Interaction Logic                -->
  <!-- ============================================== -->
  <script>
    (function initGSAPMotion() {
      // Plugins
      gsap.registerPlugin(ScrollTrigger, TextPlugin);

      // Elements Selection safely
      const loader = document.getElementById('mak-gsap-loader');
      const lockWrap = document.getElementById('loader-padlock-wrapper');
      const glow = document.getElementById('loader-glow');
      const shackle = document.getElementById('shackle-hinge');
      
      const glassPanel = document.querySelector('.glass-panel');
      const heroHeadline = document.querySelector('.glass-panel h1');
      const heroSubtitle = document.querySelector('.glass-panel p.text-xs.uppercase');

      // 1. Cursor Follower (Subtle interactivity)
      const cursorGlow = document.getElementById('gsap-cursor-glow');
      let mX = window.innerWidth / 2;
      let mY = window.innerHeight / 2;
      window.addEventListener('mousemove', e => { mX = e.clientX; mY = e.clientY; });
      gsap.ticker.add(() => {
        if (!cursorGlow) return;
        gsap.to(cursorGlow, { x: mX, y: mY, duration: 0.8, ease: 'power3.out', overwrite: 'auto' });
      });

      // 2. Prepare Page Content for Reveal (w/o breaking native layouts)
      if (glassPanel) gsap.set(glassPanel, { opacity: 0, y: 40 });
      if (heroHeadline) gsap.set(heroHeadline, { opacity: 0, y: 20 });
      
      // Safe Text Splitting for subtitle (Non-destructive to original HTML context)
      let subChars = [];
      if (heroSubtitle) {
        gsap.set(heroSubtitle, { opacity: 0 });
        const text = heroSubtitle.textContent.trim();
        heroSubtitle.textContent = ''; // clear safely
        text.split('').forEach(char => {
          const span = document.createElement('span');
          span.textContent = char === ' ' ? '\u00A0' : char;
          span.style.opacity = '0';
          span.style.transform = 'translateY(10px)';
          span.style.display = 'inline-block';
          span.style.willChange = 'transform, opacity';
          heroSubtitle.appendChild(span);
          subChars.push(span);
        });
      }

      // 3. Loader Master Timeline Feature
      // Strict rule: DO NOT obstruct execution, cleanly run > 1s then unveil
      const tl = gsap.timeline({
        onComplete: () => {
          if (loader) loader.remove();
        }
      });

      // Entry phase
      tl.fromTo(lockWrap, 
        { scale: 0.4, opacity: 0 }, 
        { scale: 1, opacity: 1, duration: 0.7, ease: 'back.out(1.5)' }
      )
      .fromTo(glow,
        { scale: 0.5, opacity: 0 },
        { scale: 1, opacity: 1, duration: 0.6, ease: 'power2.out' },
        "-=0.5"
      )
      
      // Idle Pulse & Breathing Glow (> 1s combined wait)
      .to(lockWrap, { scale: 1.05, duration: 0.45, yoyo: true, repeat: 1, ease: 'sine.inOut' })
      .to(glow, { scale: 1.25, opacity: 0.8, duration: 0.45, yoyo: true, repeat: 1, ease: 'sine.inOut' }, "-=0.9")
      
      // Unlock Moment (Shackle Hinge Opens gracefully via pure SVG)
      .to(shackle, { rotation: 45, duration: 0.5, ease: 'back.out(2)' }, "+=0.15")
      .to(glow, { scale: 1.8, opacity: 0, duration: 0.4, ease: 'power2.out' }, "-=0.4")
      
      // Exit Reveal
      .to(lockWrap, { scale: 1.15, opacity: 0, duration: 0.4, ease: 'power2.in' }, "+=0.1")
      .to(loader, { opacity: 0, duration: 0.6, ease: 'power2.inOut' }, "-=0.2")

      // 4. Page Load Animations (Post-Loader entry)
      if (glassPanel) {
        tl.to(glassPanel, { opacity: 1, y: 0, duration: 0.7, ease: 'power3.out' }, "-=0.3");
      }

      if (heroHeadline) {
        tl.to(heroHeadline, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' }, "-=0.4");
      }
      
      if (subChars.length > 0) {
        tl.to(heroSubtitle, { opacity: 1, duration: 0.1 }, "-=0.4");
        tl.to(subChars, { opacity: 1, y: 0, duration: 0.2, stagger: 0.015, ease: 'power2.out' }, "-=0.3");
      }

      // 5. Micro Interactions (Non-Destructive Hooks)
      document.querySelectorAll('.btn-3d').forEach(btn => {
        btn.addEventListener('mouseenter', () => gsap.to(btn, { scale: 1.02, duration: 0.2, overwrite: 'auto' }));
        btn.addEventListener('mouseleave', () => gsap.to(btn, { scale: 1, duration: 0.2, overwrite: 'auto' }));
        btn.addEventListener('mousedown', () => gsap.to(btn, { scale: 0.96, duration: 0.1, overwrite: 'auto' }));
        btn.addEventListener('mouseup', () => gsap.to(btn, { scale: 1.02, duration: 0.2, overwrite: 'auto' }));
      });

      document.querySelectorAll('.screenshot-input').forEach(input => {
        input.addEventListener('focus', () => gsap.to(input, { scale: 1.015, duration: 0.2, ease: 'power2.out' }));
        input.addEventListener('blur', () => gsap.to(input, { scale: 1, duration: 0.2, ease: 'power2.out' }));
      });
      
      document.querySelectorAll('.success-card, .error-card').forEach(card => {
        card.addEventListener('mouseenter', () => gsap.to(card, { y: -3, boxShadow: '0 8px 20px rgba(0,0,0,0.06)', duration: 0.3 }));
        card.addEventListener('mouseleave', () => gsap.to(card, { y: 0, boxShadow: 'none', duration: 0.3 }));
      });

      // 6. ScrollTrigger Equivalent (Safe Dynamic Steps Sequence Observer)
      // Watch when hidden sections appear and animate them gracefully inwards
      const observer = new MutationObserver((mutations) => {
        mutations.forEach((m) => {
          if (m.type === 'attributes' && m.attributeName === 'class') {
            const el = m.target;
            const wasHidden = m.oldValue && m.oldValue.includes('hidden');
            const nowVisible = !el.classList.contains('hidden');
            
            if (wasHidden && nowVisible && el.classList.contains('card-enter') && el.id !== 'toast') {
              // Ensure we cleanly animate in new steps
              gsap.fromTo(el, 
                { opacity: 0, y: 25 }, 
                { opacity: 1, y: 0, duration: 0.5, ease: 'power3.out' }
              );
              
              // Internal subtle stagger logic for sub-elements inside newly revealed sections
              const subItems = el.querySelectorAll('input, button, p, label');
              if (subItems.length > 0) {
                 gsap.fromTo(subItems, 
                   { opacity: 0, y: 15 }, 
                   { opacity: 1, y: 0, duration: 0.4, stagger: 0.04, ease: 'power2.out', delay: 0.1, clearProps: "all" }
                 );
              }
            }
          }
        });
      });

      // Attach observer to DOM components strictly
      ['step2Container', 'step3Container', 'errorCard', 'tokenInputSection', 'verifyTokenButtonSection', 'createWebmailSection', 'credentialsSection'].forEach(id => {
        const el = document.getElementById(id);
        if (el) observer.observe(el, { attributes: true, attributeOldValue: true, attributeFilter: ['class'] });
      });

      // 7. Toast Intercept (Enhances base script logic safely)
      const toastEl = document.getElementById('toast');
      if (toastEl) {
        const toastObserver = new MutationObserver(() => {
          const isShowing = !toastEl.classList.contains('translate-y-20') && !toastEl.classList.contains('opacity-0');
          if (isShowing) {
             toastEl.classList.remove('translate-y-20', 'opacity-0');
             gsap.fromTo(toastEl, { opacity: 0, y: 30 }, { opacity: 1, y: 0, duration: 0.4, ease: 'back.out(1.5)', overwrite: 'auto' });
             gsap.to(toastEl, { opacity: 0, y: 30, duration: 0.3, delay: 2.8, ease: 'power2.in', onComplete: () => {
               toastEl.classList.add('translate-y-20', 'opacity-0');
             }});
          }
        });
        toastObserver.observe(toastEl, { attributes: true, attributeFilter: ['class'] });
      }

    })();
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
