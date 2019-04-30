import Emitter from './emitter.js';

const missing = 'https://cdn.joejiko.com/img/missing.png';
// @todo exclude duplicates

const defaults = {
    selector: 'img[data-src], [data-background-src]',
};

export class ImageIntersectionObserver extends Emitter {
    update(el, src) {
        const img = el;
        if ('src' in img.dataset) img.src = src;
        if ('backgroundSrc' in img.dataset) {
            img.style.backgroundImage = `url("${src}")`;
        }
        img.dataset.srcTransform = 'loaded';
        this.trigger('loaded', img);
    }

    static fetch(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = url;
            img.onload = resolve;
            img.onerror = reject;
        });
    }

    preload(el) {
        const self = this;
        const { src: imgSrc, backgroundSrc } = el.dataset;

        if (!imgSrc && !backgroundSrc) return;
        const src = imgSrc || backgroundSrc;

        this.trigger('fetch', el);
        ImageIntersectionObserver.fetch(src)
            .then(() => {
                self.update(el, src);
            })
            .catch(() => {
                self.update(el, missing);
            });
    }

    loadImagesImmediately(els) {
        const { preload } = this;
        els.forEach((el) => {
            preload.call(this, el);
        });
    }

    onIntersection(entries) {
        if (this.count === 0) {
            this.observer.disconnect();
        }

        entries.forEach((entry) => {
            if (entry.intersectionRatio > 0) {
                this.observer.unobserve(entry.target);
                this.preload.call(this, entry.target);
            }
        });
    }

    get count() {
        return this.elements.length - (this.fetching.length + this.loaded.length);
    }

    addFetching(img) {
        this.fetching.push(img);
    }

    addLoaded(img) {
        // @todo use documentposition to compare elements in list?
        // mark as fetched
        if ('src' in img.dataset) {
            this.fetching = this.fetching.filter(item => item.src !== img.src);
        } else if ('backgroundSrc' in img.dataset) {
            this.fetching = this.fetching.filter(item => item.style.backgroundImage !== `url("${item.dataset.backgroundSrc}")`);
        }

        // add to loaded list
        this.loaded.push(img);
    }

    observe(selector) {
        if (typeof selector !== 'undefined') {
            const results = document.querySelectorAll(selector);
            if (results.length) {
                this.elements = Array.from(results)
                    .filter(e => ('src' in e.dataset) || ('backgroundSrc' in e.dataset));
            }
        }

        if (this.elements.length) {
            this.elements.forEach((elem) => {
                if (!('srcTransform' in elem.dataset) && elem.dataset.srcTransform !== 'loaded') {
                    this.observer.observe(elem);
                }
            });
        }
    }

    constructor(target) {
        super();

        if (typeof target !== 'undefined') {
            this.elements = Array.from(target.querySelectorAll(defaults.selector));
        } else {
            this.elements = Array.from(document.querySelectorAll(defaults.selector));
        }

        this.fetching = [];
        this.loaded = [];

        if (!Object.hasOwnProperty.call(window, 'IntersectionObserver')) {
            this.loadImagesImmediately(this.elements);
            return;
        }

        this.on('fetch', this.addFetching);
        this.on('loaded', this.addLoaded);

        this.observer = new IntersectionObserver(this.onIntersection.bind(this), {
            rootMargin: '50px 0px',
            threshold: 0.01,
        });

        this.observe(defaults.selector);
    }
}

export { ImageIntersectionObserver as default };
