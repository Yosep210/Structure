import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon({
    className,
    alt = 'App Logo',
    ...props
}: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img src="/favicon.svg" alt={alt} className={className} {...props} />
    );
}
