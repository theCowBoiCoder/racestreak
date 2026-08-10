<template>
  <main>
    <section class="card registration-card">
      <p class="eyebrow">Join RaceStreak</p>
      <h1>Create your driver account</h1>
      <p class="intro">Start building a sim-racing profile where every race counts.</p>

      <DriverRegistrationForm />
    </section>
  </main>
</template>

<style scoped>
h1 {
  font-size: clamp(2.4rem, 8vw, 4.75rem);
}

.registration-card {
  width: min(100%, 46rem);
}
</style>
