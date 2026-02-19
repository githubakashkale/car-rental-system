// Firebase Configuration for RentRide
// Project: rentride-907e1

const firebaseConfig = {
    apiKey: "AIzaSyBsDk4ATdY8ZDNklPlpZBdQuXk5uVrOhLo",
    authDomain: "rentride-907e1.firebaseapp.com",
    projectId: "rentride-907e1",
    storageBucket: "rentride-907e1.firebasestorage.app",
    messagingSenderId: "910983784411",
    appId: "1:910983784411:web:1749dc23f9482813f129fe",
    measurementId: "G-CV0FPDZZLB"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();

// Set language to user's browser language
auth.languageCode = 'en';
