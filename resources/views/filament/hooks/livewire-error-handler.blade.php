<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    window.location.reload();
                    return;
                }

                if (status === 503) {
                    preventDefault();

                    const minimumCooldownMilliseconds = 30000;
                    const lastReloadTimestamp = sessionStorage.getItem('livewire_503_last_reload_at');
                    const currentTimestamp = Date.now();

                    const isCooldownActive = lastReloadTimestamp !== null
                        && (currentTimestamp - parseInt(lastReloadTimestamp, 10)) < minimumCooldownMilliseconds;

                    if (isCooldownActive) {
                        return;
                    }

                    sessionStorage.setItem('livewire_503_last_reload_at', currentTimestamp.toString());
                    window.location.reload();
                }
            });
        });
    });
</script>
