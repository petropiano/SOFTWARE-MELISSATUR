// Passo A: Importar as funções necessárias do Firebase
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-app.js";
import { getAuth, createUserWithEmailAndPassword, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-auth.js";
import { getFirestore, doc, setDoc } from "https://www.gstatic.com/firebasejs/9.15.0/firebase-firestore.js";

// Passo B: Configuração do seu projeto Firebase (já preenchida com suas informações)
const firebaseConfig = {
  apiKey: "AIzaSyDJCBLX9l4YR4_jIRrio3devorkDv015rw",
  authDomain: "melissa-tur.firebaseapp.com",
  projectId: "melissa-tur",
  storageBucket: "melissa-tur.appspot.com", // Corrigido para o formato padrão do Firebase
  messagingSenderId: "76250176887",
  appId: "1:76250176887:web:975706ee32f8bec938d572",
  measurementId: "G-9YV69PBJ20"
};

// Passo C: Inicializar o Firebase e os serviços que vamos usar
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);      // Serviço de Autenticação
const db = getFirestore(app);   // Serviço de Banco de Dados

// --- Código de controle da página HTML ---

const loginForm = document.getElementById('loginForm');
const signupForm = document.getElementById('signupForm');
const showSignupLink = document.getElementById('showSignup');
const showLoginLink = document.getElementById('showLogin');
const toggleToSignup = document.getElementById('toggleToSignup');
const toggleToLogin = document.getElementById('toggleToLogin');
const messageEl = document.getElementById('message');
const cpfInput = document.getElementById('signup-cpf');
const dobInput = document.getElementById('signup-dob');

const hoje = new Date();
const ano = hoje.getFullYear();
const mes = String(hoje.getMonth() + 1).padStart(2, '0');
const dia = String(hoje.getDate()).padStart(2, '0');
const dataMaxima = `${ano}-${mes}-${dia}`;
dobInput.setAttribute('max', dataMaxima);

function clearMessage() {
    messageEl.textContent = '';
    messageEl.className = 'message';
}

showSignupLink.addEventListener('click', (e) => { e.preventDefault(); clearMessage(); loginForm.classList.add('hidden'); signupForm.classList.remove('hidden'); toggleToSignup.classList.add('hidden'); toggleToLogin.classList.remove('hidden'); });
showLoginLink.addEventListener('click', (e) => { e.preventDefault(); clearMessage(); signupForm.classList.add('hidden'); loginForm.classList.remove('hidden'); toggleToLogin.classList.add('hidden'); toggleToSignup.classList.remove('hidden'); });

cpfInput.addEventListener('input', () => {
    let value = cpfInput.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    cpfInput.value = value;
});

loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearMessage();
    const email = document.getElementById('login-email').value;
    const senha = document.getElementById('login-password').value;

    try {
        const userCredential = await signInWithEmailAndPassword(auth, email, senha);
        messageEl.textContent = 'Login efetuado com sucesso! Redirecionando...';
        messageEl.className = 'message success';
    } catch (error) {
        messageEl.textContent = 'E-mail ou senha inválidos.';
        messageEl.className = 'message error';
        console.error("Erro de login:", error.message);
    }
});

signupForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    clearMessage();

    const dataNascimento = new Date(dobInput.value);
    const dataLimite = new Date();
    dataLimite.setFullYear(dataLimite.getFullYear() - 12);
    if (dataNascimento > dataLimite) {
        messageEl.textContent = 'É necessário ter no mínimo 12 anos para se cadastrar.';
        messageEl.className = 'message error';
        return;
    }

    const nome = document.getElementById('signup-name').value;
    const email = document.getElementById('signup-email').value;
    const senha = document.getElementById('signup-password').value;
    const cpf = document.getElementById('signup-cpf').value;
    const dob = document.getElementById('signup-dob').value;

    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, senha);
        const user = userCredential.user;

        await setDoc(doc(db, "usuarios", user.uid), {
            nome_completo: nome,
            cpf: cpf,
            data_nascimento: dob,
            email: user.email
        });

        messageEl.textContent = 'Cadastro realizado com sucesso!';
        messageEl.className = 'message success';
        signupForm.reset();

    } catch (error) {
        if (error.code === 'auth/email-already-in-use') {
            messageEl.textContent = 'Este e-mail já está em uso.';
        } else {
            messageEl.textContent = 'Erro ao cadastrar. Verifique os dados.';
        }
        messageEl.className = 'message error';
        console.error("Erro de cadastro:", error.message);
    }
});
