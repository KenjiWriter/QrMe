export type CustomQrType = 'url' | 'wifi';

export type WifiEncryption = 'WPA' | 'WEP' | 'WPA-EAP' | 'nopass';

export type WifiEapMethod = 'PEAP' | 'TTLS' | 'PWD';

export type CustomQrItem = {
    id: number;
    short_id: string;
    name: string;
    type: CustomQrType;
    url: string | null;
    wifi_ssid: string | null;
    /** Only sent on the edit page. */
    wifi_password: string | null;
    wifi_encryption: WifiEncryption | null;
    wifi_hidden: boolean;
    wifi_eap_method: WifiEapMethod | null;
    wifi_eap_identity: string | null;
    qr_color: string;
    qr_eye_shape: string;
    logo_url: string | null;
    qr_code_url: string | null;
    scan_url: string;
    scan_count: number;
};
