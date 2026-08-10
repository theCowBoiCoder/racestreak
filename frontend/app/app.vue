<template>
  <NuxtRouteAnnouncer />
  <NuxtPage />
</template>

<style>
:root {
  color-scheme: dark;
  font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    'Segoe UI',
    sans-serif;
  background: #090b10;
  color: #f6f7fb;
}

* {
  box-sizing: border-box;
}
body {
  margin: 0;
}

main {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 2rem;
  background:
    radial-gradient(circle at 20% 20%, rgba(255, 82, 46, 0.18), transparent 35%),
    linear-gradient(145deg, #111522, #07080c 65%);
}

.card {
  width: min(100%, 42rem);
  padding: clamp(2rem, 7vw, 4.5rem);
  border: 1px solid rgba(255, 255, 255, 0.11);
  border-radius: 1.5rem;
  background: rgba(15, 18, 27, 0.82);
  box-shadow: 0 2rem 6rem rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(18px);
}

.eyebrow {
  margin: 0 0 0.75rem;
  color: #ff6647;
  font-weight: 750;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}
h1 {
  margin: 0;
  font-size: clamp(3rem, 10vw, 6rem);
  line-height: 0.95;
  letter-spacing: -0.055em;
}
.intro {
  margin: 1.25rem 0 2.5rem;
  color: #adb3c1;
  font-size: 1.15rem;
}
</style>
