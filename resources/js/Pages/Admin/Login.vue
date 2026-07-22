<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, KeyRound, UserRound } from '@lucide/vue';

defineProps({
    profile: { type: Object, required: true },
});

const form = useForm({ token: '' });

function submit() {
    form.post('/admin/login', {
        onFinish: () => form.reset('token'),
    });
}
</script>

<template>
    <Head title="Portfolio admin">
        <link v-if="profile.avatar_url" rel="icon" :href="profile.avatar_url">
    </Head>

    <main class="login-shell">
        <section class="login-panel">
            <a class="wordmark" href="/">
                <span class="wordmark-photo">
                    <UserRound :size="18" />
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                </span>
                <span>Portfolio</span>
            </a>
            <div class="login-copy">
                <span class="index">Private index / 01</span>
                <h1>Portfolio admin</h1>
                <p>Enter the private access token to manage the public archive.</p>
            </div>

            <form @submit.prevent="submit">
                <label for="token">Secret token</label>
                <div class="token-field" :class="{ invalid: form.errors.token }">
                    <KeyRound :size="17" />
                    <input
                        id="token"
                        v-model="form.token"
                        type="password"
                        name="token"
                        autocomplete="current-password"
                        autofocus
                        required
                    >
                </div>
                <p v-if="form.errors.token" class="error">{{ form.errors.token }}</p>
                <button type="submit" :disabled="form.processing">
                    <span>{{ form.processing ? 'Checking' : 'Enter admin' }}</span>
                    <ArrowRight :size="16" />
                </button>
            </form>
        </section>
    </main>
</template>

<style scoped>
.login-shell {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 1.25rem;
    background:
        linear-gradient(var(--profile-200) 1px, transparent 1px),
        linear-gradient(90deg, var(--profile-200) 1px, transparent 1px),
        var(--profile-bg);
    background-size: 48px 48px;
}

.login-panel {
    width: min(100%, 31rem);
    border: 1px solid var(--profile-ink);
    background: var(--profile-bg);
    padding: clamp(1.4rem, 5vw, 2.6rem);
    box-shadow: 12px 12px 0 var(--profile-ink);
}

.wordmark,
.index,
label,
button {
    font-family: var(--font-mono);
    text-transform: uppercase;
}

.wordmark {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    gap: 0.65rem;
    color: var(--profile-ink);
    font-size: 0.75rem;
    text-decoration: none;
}

.wordmark-photo {
    position: relative;
    display: grid;
    width: 2.25rem;
    aspect-ratio: 1;
    place-items: center;
    overflow: hidden;
    border-radius: 50%;
    background: var(--profile-ink);
    color: var(--profile-bg);
}

.wordmark-photo img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center top;
}

.login-copy {
    margin: 4.8rem 0 2.2rem;
}

.index {
    color: var(--profile-500);
    font-size: 0.68rem;
}

h1 {
    margin: 0.6rem 0 0;
    font-size: clamp(2.2rem, 8vw, 4rem);
    line-height: 0.95;
}

p {
    color: var(--profile-700);
    line-height: 1.6;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.68rem;
}

.token-field {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    border: 1px solid var(--profile-300);
    padding: 0 0.8rem;
}

.token-field:focus-within {
    border-color: var(--profile-ink);
}

.token-field.invalid {
    border-color: #b42318;
}

input {
    min-width: 0;
    width: 100%;
    border: 0;
    outline: 0;
    background: transparent;
    padding: 0.8rem 0;
    color: var(--profile-ink);
}

.error {
    margin: 0.45rem 0 0;
    color: #b42318;
    font-size: 0.78rem;
}

button {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--profile-ink);
    margin-top: 1rem;
    background: var(--profile-ink);
    padding: 0.8rem 0.9rem;
    color: var(--profile-bg);
    cursor: pointer;
}

button:disabled {
    cursor: wait;
    opacity: 0.6;
}
</style>
