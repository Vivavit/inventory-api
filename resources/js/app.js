import './bootstrap';
import Alpine from 'alpinejs';
import './sidebar';
import './charts';
import { registerAnalyticsApp } from './features/analytics';
 
window.Alpine = Alpine;
registerAnalyticsApp();
Alpine.start();
 