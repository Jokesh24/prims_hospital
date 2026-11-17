<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,900;1,700&display=swap"
    rel="stylesheet" />
  <style>
    html {
      height: 100%;
    }

    body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Nunito', sans-serif;
      display: grid;
      grid-template-columns: 1fr 460px;
      grid-template-rows: 100vh;
      overflow: hidden;
    }

    /* ─── LEFT ─── */
    .left {
      background: #1E3A8A;
      display: flex;
      flex-direction: column;
      padding: 48px 56px;
      position: relative;
      overflow: hidden;
    }

    /* decorative rings */
    .left::after {
      content: '';
      position: absolute;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      border: 56px solid rgba(245, 158, 11, .13);
      bottom: -160px;
      right: -160px;
      pointer-events: none;
    }

    .left::before {
      content: '';
      position: absolute;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      border: 30px solid rgba(245, 158, 11, .2);
      bottom: -60px;
      right: -60px;
      pointer-events: none;
      z-index: 0;
    }

    .left>* {
      position: relative;
      z-index: 1;
    }

    /* logo */
    .logo {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .shield {
      width: 48px;
      height: 54px;
      flex-shrink: 0;
      background: #F59E0B;
      clip-path: polygon(0 0, 100% 0, 100% 68%, 50% 100%, 0 68%);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .school-name {
      font-family: 'Playfair Display', serif;
      font-size: 18px;
      font-weight: 900;
      color: #fff;
      line-height: 1.2;
    }

    .school-since {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: #F59E0B;
      margin-top: 2px;
    }

    /* hero */
    .hero {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, .1);
      border: 1px solid rgba(255, 255, 255, .18);
      border-radius: 6px;
      padding: 5px 12px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: #F59E0B;
      width: fit-content;
      margin-bottom: 24px;
    }

    .hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: 42px;
      font-weight: 900;
      line-height: 1.1;
      color: #fff;
      margin-bottom: 16px;
      letter-spacing: -.5px;
    }

    .hero h1 em {
      font-style: italic;
      color: #F59E0B;
    }

    .hero p {
      font-size: 15px;
      color: rgba(255, 255, 255, .58);
      line-height: 1.7;
      max-width: 340px;
      margin-bottom: 28px;
    }

    .pills {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .pill {
      display: flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, .09);
      border: 1px solid rgba(255, 255, 255, .15);
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 700;
      color: rgba(255, 255, 255, .8);
    }

    .dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
    }

    /* stats */
    .stats {
      display: flex;
      gap: 32px;
      padding-top: 24px;
      border-top: 1px solid rgba(255, 255, 255, .12);
    }

    .s-num {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: #F59E0B;
    }

    .s-lbl {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: rgba(255, 255, 255, .4);
      margin-top: 2px;
    }

    /* ─── RIGHT ─── */
    .right {
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 36px;
      overflow-y: auto;
      box-shadow: -4px 0 30px rgba(30, 58, 138, .12);
    }

    .form-box {
      width: 100%;
      max-width: 340px;
    }

    /* mini brand */
    .mini-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 28px;
    }

    .mini-shield {
      width: 30px;
      height: 34px;
      flex-shrink: 0;
      background: #1E3A8A;
      clip-path: polygon(0 0, 100% 0, 100% 68%, 50% 100%, 0 68%);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .mini-name {
      font-family: 'Playfair Display', serif;
      font-size: 14px;
      font-weight: 700;
      color: #1E3A8A;
      line-height: 1.2;
    }

    .mini-tag {
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: #F59E0B;
    }

    /* heading */
    .f-eye {
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .12em;
      text-transform: uppercase;
      color: #1E3A8A;
      margin-bottom: 6px;
    }

    .f-title {
      font-family: 'Playfair Display', serif;
      font-size: 28px;
      font-weight: 900;
      color: #0F172A;
      margin-bottom: 4px;
    }

    .f-sub {
      font-size: 13px;
      color: #64748B;
      margin-bottom: 22px;
    }

    /* role tabs */
    .role-row {
      display: flex;
      gap: 4px;
      background: #F1F5F9;
      border: 1.5px solid #E2E8F0;
      border-radius: 10px;
      padding: 4px;
      margin-bottom: 20px;
    }

    .rtab {
      flex: 1;
      border: none;
      background: transparent;
      font-family: 'Nunito', sans-serif;
      font-size: 11px;
      font-weight: 800;
      color: #94A3B8;
      padding: 7px 4px;
      border-radius: 7px;
      cursor: pointer;
      transition: all .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }

    .rtab.on {
      background: #fff;
      color: #1E3A8A;
      box-shadow: 0 1px 6px rgba(0, 0, 0, .1);
    }

    .rtab:hover:not(.on) {
      color: #1E3A8A;
    }

    /* fields */
    .fld {
      margin-bottom: 14px;
    }

    .flbl {
      display: block;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #94A3B8;
      margin-bottom: 6px;
    }

    .fwrap {
      position: relative;
    }

    .fwrap .ficon {
      position: absolute;
      left: 13px;
      top: 50%;
      transform: translateY(-50%);
      color: #CBD5E1;
      pointer-events: none;
      display: flex;
    }

    .fwrap input {
      width: 100%;
      background: #F8FAFC;
      border: 1.5px solid #E2E8F0;
      border-radius: 10px;
      color: #0F172A;
      font-family: 'Nunito', sans-serif;
      font-size: 14px;
      font-weight: 600;
      padding: 11px 14px 11px 38px;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }

    .fwrap input::placeholder {
      color: #CBD5E1;
      font-weight: 500;
    }

    .fwrap input:focus {
      border-color: #1E3A8A;
      box-shadow: 0 0 0 3px rgba(30, 58, 138, .12);
      background: #fff;
    }

    .fwrap .eyebtn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      padding: 0;
      cursor: pointer;
      color: #CBD5E1;
      display: flex;
      transition: color .15s;
    }

    .fwrap .eyebtn:hover {
      color: #1E3A8A;
    }

    /* meta row */
    .meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .form-check-label {
      font-size: 13px;
      font-weight: 600;
      color: #64748B;
    }

    .form-check-input:checked {
      background-color: #1E3A8A;
      border-color: #1E3A8A;
    }

    .form-check-input:focus {
      box-shadow: 0 0 0 3px rgba(30, 58, 138, .15);
    }

    .flnk {
      font-size: 13px;
      font-weight: 700;
      color: #1E3A8A;
      text-decoration: none;
    }

    .flnk:hover {
      text-decoration: underline;
    }

    /* button */
    .btn-in {
      width: 100%;
      padding: 13px;
      background: #1E3A8A;
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: 'Nunito', sans-serif;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      letter-spacing: .02em;
      box-shadow: 0 4px 18px rgba(30, 58, 138, .3);
      transition: background .15s, box-shadow .15s, transform .1s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-in:hover {
      background: #162D6E;
      box-shadow: 0 6px 24px rgba(30, 58, 138, .4);
    }

    .btn-in:active {
      transform: scale(.98);
    }

    /* notice */
    .notice {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      background: #FEF3C7;
      border: 1px solid #FDE68A;
      border-radius: 9px;
      padding: 10px 13px;
      margin-top: 14px;
      font-size: 12px;
      font-weight: 600;
      color: #92400E;
      line-height: 1.5;
    }

    .notice svg {
      flex-shrink: 0;
      margin-top: 1px;
    }

    /* or divider */
    .or {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 16px 0 12px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #CBD5E1;
    }

    .or::before,
    .or::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #E2E8F0;
    }

    /* sso */
    .sso {
      display: flex;
      gap: 8px;
    }

    .ssob {
      flex: 1;
      padding: 9px 6px;
      background: #F8FAFC;
      border: 1.5px solid #E2E8F0;
      border-radius: 9px;
      font-family: 'Nunito', sans-serif;
      font-size: 11px;
      font-weight: 700;
      color: #0F172A;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: border-color .15s;
    }

    .ssob:hover {
      border-color: #1E3A8A;
    }

    /* footer */
    .ffoot {
      text-align: center;
      font-size: 11px;
      font-weight: 600;
      color: #94A3B8;
      margin-top: 16px;
    }

    .ffoot a {
      color: #1E3A8A;
      font-weight: 700;
      text-decoration: none;
    }

    .ffoot a:hover {
      text-decoration: underline;
    }

    @media (max-width: 900px) {
      body {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
        overflow: auto;
      }

      .left {
        display: none;
      }

      .right {
        min-height: 100vh;
      }
    }
  </style>
</head>

<body>

  <!-- LEFT -->
  <div class="left">


    <div class="hero">
      <div class="badge">
        <svg width="8" height="8" viewBox="0 0 8 8">
          <circle cx="4" cy="4" r="4" fill="#F59E0B" />
        </svg>
        HRMS Portal
      </div>
      <h1>Empowering<br /><em>business's</em><br />one click away</h1>
      <p>Attendance, payroll, leave & performance — all in one portal built for business's that put you first.</p>

    </div>

    <div class="stats">
      <div>
        <div class="s-num">248</div>
        <div class="s-lbl">Staff Members</div>
      </div>
      <div>
        <div class="s-num">96.2%</div>
        <div class="s-lbl">Attendance</div>
      </div>
      <div>
        <div class="s-num">18</div>
        <div class="s-lbl">Departments</div>
      </div>
      <!-- <div>
        <div class="s-num">1,840</div>
        <div class="s-lbl">Students</div>
      </div> -->
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
      style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #059669; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
      <i class="bi bi-check-circle-fill" style="color: #059669; margin-right: 8px;"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
        style="font-size: 0.8rem;"></button>
    </div>
  @endif

  @if(session('error') || $errors->any())
    <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
      style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #dc2626; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
      <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626; margin-right: 8px;"></i>
      {{ session('error') ?? $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
        style="font-size: 0.8rem;"></button>
    </div>
  @endif

  <!-- RIGHT -->
  <div class="right">
    <div class="form-box">
      <div class="f-eye">Employee Portal</div>
      <div class="f-title">Sign in to HRMS</div>
      <div class="f-sub">Use your Staff ID or school email to continue.</div>

      @if($errors->has('error'))
        <div class="alert alert-danger p-2 mb-3" style="font-size: 13px;">
          {{ $errors->first('error') }}
        </div>
      @endif

      <form action="{{ route('employee.authenticate') }}" method="POST">
        @csrf

        <div class="fld">
          <label class="flbl">Staff ID / Email</label>
          <div class="fwrap">
            <span class="ficon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <circle cx="9" cy="10" r="2" />
                <path d="M15 8h2M15 12h2M5 16h14" />
              </svg>
            </span>
            <input type="text" name="employee_email" value="{{ old('employee_email') }}"
              placeholder="STF-0042 or name@greenwood.edu" required />
          </div>
          @error('employee_email')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="fld">
          <label class="flbl">Password</label>
          <div class="fwrap">
            <span class="ficon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <rect x="3" y="11" width="18" height="11" rx="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
            </span>
            <input type="password" name="employee_password" id="pwd" placeholder="Enter your password"
              style="padding-right:42px" required />
            <button class="eyebtn" onclick="togglePwd()" type="button">
              <svg id="eyeico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </button>
          </div>
          @error('employee_password')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="meta">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rem" />
            <label class="form-check-label" for="rem">Keep me signed in</label>
          </div>
          <a href="#" class="flnk">Forgot password?</a>
        </div>

        <button class="btn-in" type="submit">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
            stroke-linecap="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
            <polyline points="10 17 15 12 10 7" />
            <line x1="15" y1="12" x2="3" y2="12" />
          </svg>
          Sign in
        </button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function togglePwd() {
      const p = document.getElementById('pwd');
      const i = document.getElementById('eyeico');
      if (p.type === 'password') {
        p.type = 'text';
        i.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;
      } else {
        p.type = 'password';
        i.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
      }
    }
    function pick(el) {
      document.querySelectorAll('.rtab').forEach(b => b.classList.remove('on'));
      el.classList.add('on');
    }
  </script>
</body>

</html>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Find all floating alerts
    let alerts = document.querySelectorAll('.floating-alert');

    if (alerts.length > 0) {
      // Wait 5 seconds, then fade them out
      setTimeout(function () {
        alerts.forEach(function (alertNode) {
          // Check if Bootstrap JS is loaded and use its Alert API to dismiss smoothly
          if (typeof bootstrap !== 'undefined') {
            let bsAlert = new bootstrap.Alert(alertNode);
            bsAlert.close();
          } else {
            // Fallback if bootstrap JS isn't initialized yet
            alertNode.style.display = 'none';
          }
        });
      }, 5000); // 5000ms = 5 seconds
    }
  });
</script>