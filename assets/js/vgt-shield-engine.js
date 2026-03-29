/**
 * VISIONGAIATECHNOLOGY SHIELD ENGINE
 * STATUS: DIAMANT SUPREME
 * TYPE: BACKGROUND MINING & ROBUST NETWORK LAYER INTERCEPTION
 */
"use strict";

document.addEventListener('DOMContentLoaded', () => {
    const VGTSecurity = {
        currentProof: null,
        isMining: false,

        // VGT Supreme: Fallback Sync SHA256 ESNext Strict Mode
        syncSha256(ascii) {
            const rightRotate = (value, amount) => (value >>> amount) | (value << (32 - amount));
            const mathPow = Math.pow, maxWord = mathPow(2, 32);
            let result = '';
            const words = [], asciiBitLength = ascii.length * 8;
            
            this.h = this.h || []; this.k = this.k || [];
            let hash = this.h, k = this.k, primeCounter = k.length;
            const isComposite = {};
            
            for (let candidate = 2; primeCounter < 64; candidate++) {
                if (!isComposite[candidate]) {
                    for (let i = 0; i < 313; i += candidate) isComposite[i] = candidate;
                    hash[primeCounter] = (mathPow(candidate, .5) * maxWord) | 0;
                    k[primeCounter++] = (mathPow(candidate, 1 / 3) * maxWord) | 0;
                }
            }
            ascii += '\x80';
            while (ascii.length % 64 - 56) ascii += '\x00';
            for (let i = 0; i < ascii.length; i++) {
                const j = ascii.charCodeAt(i);
                if (j >> 8) return; 
                words[i >> 2] |= j << ((3 - i % 4) * 8);
            }
            words[words.length] = ((asciiBitLength / maxWord) | 0);
            words[words.length] = asciiBitLength;
            for (let j = 0; j < words.length;) {
                const w = words.slice(j, j += 16);
                const oldHash = hash.slice(0);
                hash = hash.slice(0, 8);
                for (let i = 0; i < 64; i++) {
                    const w15 = w[i - 15], w2 = w[i - 2], a = hash[0], e = hash[4];
                    const temp1 = hash[7] + (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25)) + ((e & hash[5]) ^ ((~e) & hash[6])) + k[i] + (w[i] = (i < 16) ? w[i] : (w[i - 16] + (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15 >>> 3)) + w[i - 7] + (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2 >>> 10))) | 0);
                    const temp2 = (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22)) + ((a & hash[1]) ^ (a & hash[2]) ^ (hash[1] & hash[2]));
                    hash = [(temp1 + temp2) | 0].concat(hash);
                    hash[4] = (hash[4] + temp1) | 0;
                }
                for (let i = 0; i < 8; i++) hash[i] = (hash[i] + oldHash[i]) | 0;
            }
            for (let i = 0; i < 8; i++) {
                for (let j = 3; j + 1; j--) {
                    const b = (hash[i] >> (j * 8)) & 255;
                    result += ((b < 16) ? 0 : '') + b.toString(16);
                }
            }
            return result;
        },

        async fetchChallenge() {
            try {
                const response = await fetch(vgtShieldConfig.apiUrl);
                if (!response.ok) return null;
                return await response.json();
            } catch (error) { return null; }
        },

        async mineChallenge() {
            if (this.isMining) return;
            this.isMining = true;
            
            const challenge = await this.fetchChallenge();
            if (!challenge) {
                this.isMining = false;
                return;
            }

            return new Promise((resolve) => {
                const worker = new Worker(vgtShieldConfig.workerUrl);
                
                worker.onmessage = (e) => {
                    this.currentProof = e.data;
                    this.isMining = false;
                    worker.terminate();
                    resolve(e.data);
                };
                
                worker.onerror = () => {
                    worker.terminate();
                    this.mineFallback(challenge).then(proof => {
                        this.currentProof = proof;
                        this.isMining = false;
                        resolve(proof);
                    });
                };
                worker.postMessage(challenge);
            });
        },

        async mineFallback(challenge) {
            let nonce = 0;
            const target = '0'.repeat(challenge.difficulty);
            while (true) {
                const hashHex = this.syncSha256(challenge.seed + nonce);
                if (hashHex.startsWith(target)) return { ...challenge, nonce: nonce };
                nonce++;
                if (nonce % 1000 === 0) await new Promise(r => setTimeout(r, 0)); 
            }
        },

        consumeProof() {
            const proof = this.currentProof;
            this.currentProof = null;
            this.mineChallenge(); 
            return proof;
        },

        // VGT Supreme: Hermetisch gekapseltes Network Layer Hijacking
        interceptNetwork() {
            const isTargetUrl = (url) => url && (url.includes('wp-json') || url.includes('admin-ajax.php') || url.includes('wc-ajax=') || url.includes('/?wc-ajax='));

            // 1. Fetch API Interceptor (Safe Override - VGT SUPREME)
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                try {
                    let url = '';
                    let requestObj = null;

                    if (args[0] instanceof Request) {
                        // Klonen des Requests erzwingen, um 'already been used' Fehler bei POST-Bodies zu eliminieren.
                        requestObj = args[0].clone(); 
                        url = requestObj.url;
                    } else {
                        url = typeof args[0] === 'string' ? args[0] : (args[0] && args[0].url ? args[0].url : '');
                    }
                    
                    if (isTargetUrl(url)) {
                        while (!this.currentProof && this.isMining) await new Promise(r => setTimeout(r, 50));
                        
                        const proof = this.consumeProof();
                        if (proof) {
                            if (requestObj) {
                                const newHeaders = new Headers(requestObj.headers);
                                newHeaders.append('X-VGT-Shield-PoW', JSON.stringify(proof));
                                // Rekonstruktion mit geklontem Body und neuen Headern
                                args[0] = new Request(requestObj, { 
                                    headers: newHeaders,
                                    body: requestObj.body 
                                });
                            } else {
                                args[1] = args[1] || {};
                                args[1].headers = args[1].headers || {};
                                
                                if (args[1].headers instanceof Headers) {
                                    args[1].headers.append('X-VGT-Shield-PoW', JSON.stringify(proof));
                                } else if (Array.isArray(args[1].headers)) {
                                    args[1].headers.push(['X-VGT-Shield-PoW', JSON.stringify(proof)]);
                                } else {
                                    args[1].headers['X-VGT-Shield-PoW'] = JSON.stringify(proof);
                                }
                            }
                        }
                    }
                } catch (e) {
                    console.error('VGT-SHIELD-CRITICAL: Fetch Interception Failure.', e);
                }
                return originalFetch.apply(window, args);
            };

            // 2. XMLHttpRequest Interceptor (Safe Override - Prototype Freeze Bypass)
            try {
                if (Object.isFrozen(XMLHttpRequest.prototype)) {
                    console.warn('VGT-SHIELD: XHR Prototype frozen. Hijacking limited.');
                    return; // Fail gracefully anstatt SPA Runtime zu crashen
                }
                
                const originalOpen = XMLHttpRequest.prototype.open;
                const originalSend = XMLHttpRequest.prototype.send;
                
                XMLHttpRequest.prototype.open = function(method, url, ...rest) {
                    try { this._vgtUrl = url; } catch(e) {}
                    return originalOpen.call(this, method, url, ...rest);
                };
                
                const vgtInstance = this;
                XMLHttpRequest.prototype.send = function(body) {
                    try {
                        if (isTargetUrl(this._vgtUrl)) {
                            const proof = vgtInstance.consumeProof();
                            if (proof) {
                                this.setRequestHeader('X-VGT-Shield-PoW', JSON.stringify(proof));
                            }
                        }
                    } catch(e) {
                        console.warn('VGT-SHIELD: XHR Interception Non-Fatal Error.', e);
                    }
                    return originalSend.call(this, body);
                };
            } catch (e) {
                console.error('VGT-SHIELD-CRITICAL: XHR Mutation Error.', e);
            }
        },

        init() {
            this.interceptNetwork();
            this.mineChallenge();

            document.addEventListener('submit', async (e) => {
                const form = e.target;
                if (form.dataset.vgtSecured === 'true' || form.querySelector('input[name="vgt_pow_payload"]')) return;

                e.preventDefault();
                const btn = form.querySelector('button[type="submit"], input[type="submit"]');
                const originalText = btn ? (btn.value || btn.innerText) : '';
                
                if (btn) {
                    if(btn.value) btn.value = 'VGT Handshake...';
                    else btn.innerText = 'VGT Handshake...';
                    btn.style.opacity = '0.5';
                    btn.style.pointerEvents = 'none';
                }

                while (!this.currentProof && this.isMining) await new Promise(r => setTimeout(r, 50));
                
                const proof = this.consumeProof();
                if (proof) {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'vgt_pow_payload';
                    input.value = JSON.stringify(proof);
                    form.appendChild(input);
                }

                form.dataset.vgtSecured = 'true';
                
                if (btn) {
                    if(btn.value) btn.value = originalText;
                    else btn.innerText = originalText;
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                    btn.click();
                } else {
                    form.submit();
                }
            });
        }
    };
    VGTSecurity.init();
});