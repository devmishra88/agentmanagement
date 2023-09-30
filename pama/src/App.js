import logo from './logo.svg';
import './App.css';
import Login from './screens/Login';
import Forgot from './screens/Forgot'
import { BrowserRouter, Route, Routes, Switch } from 'react-router-dom';


function App() {
  return (
    <div className="App">
    <BrowserRouter>
    <Routes>
    <Route path="/" element={<Login />} />
          <Route path="forgot" element={<Forgot />} />
    </Routes>


      
    </BrowserRouter>
    </div>
  );
}

export default App;
