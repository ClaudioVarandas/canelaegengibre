export default function carousel(total) {
    return {
        active: 0,
        pages: 1,
        total,
        perPage() {
            if (window.innerWidth >= 1024) return 4;
            if (window.innerWidth >= 640) return 2;
            return 1;
        },
        stride() {
            const first = this.$refs.track.firstElementChild;
            return first ? first.offsetWidth + 16 : this.$refs.track.clientWidth;
        },
        init() {
            this.recompute();
            window.addEventListener('resize', () => this.recompute());
            this.$refs.track.addEventListener('scroll', () => this.sync());
        },
        recompute() {
            this.pages = Math.max(1, Math.ceil(this.total / this.perPage()));
            this.sync();
        },
        sync() {
            const s = this.stride();
            if (s <= 0) {
                this.active = 0;
                return;
            }
            const cardIndex = Math.round(this.$refs.track.scrollLeft / s);
            this.active = Math.min(this.pages - 1, Math.floor(cardIndex / this.perPage()));
        },
        goto(i) {
            const target = Math.max(0, Math.min(this.pages - 1, i));
            this.active = target;
            this.$refs.track.scrollTo({
                left: target * this.stride() * this.perPage(),
                behavior: 'smooth',
            });
        },
    };
}
