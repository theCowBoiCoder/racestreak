<template>
  <main>
    <section class="card">
      <p class="eyebrow">Every race counts.</p>
      <h1>RaceStreak</h1>
      <p class="intro">The platform foundation is running.</p>

      <HealthStatus />

      <NuxtLink class="primary-link" to="/register">Create your driver account</NuxtLink>
    </section>
  </main>
</template>

<style scoped>
.primary-link {
  display: inline-block;
  margin-top: 1.25rem;
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
  background: #ff6647;
  color: #fff;
  font-weight: 800;
  text-decoration: none;
}
</style>
