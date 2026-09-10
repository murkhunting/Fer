// Sincroniza public/proyectos-data con el servidor por FTP.
// Uso: FTP_HOST=... FTP_USER=... FTP_PASS=... FTP_DIR=/public_html/proyectos-data node scripts/sync.mjs down|up
// down: servidor -> local (para depurar con datos reales)
// up:   local   -> servidor (solo seed inicial; NUNCA con datos del cliente encima)
import * as ftp from "basic-ftp";

const mode = process.argv[2];
if (!["down", "up"].includes(mode)) {
  console.error("Uso: node scripts/sync.mjs down|up");
  process.exit(1);
}

const { FTP_HOST, FTP_USER, FTP_PASS, FTP_DIR = "/public_html/proyectos-data" } = process.env;
if (!FTP_HOST || !FTP_USER || !FTP_PASS) {
  console.error("Faltan FTP_HOST / FTP_USER / FTP_PASS en el entorno (.env).");
  process.exit(1);
}

const client = new ftp.Client(10000);
try {
  await client.access({ host: FTP_HOST, user: FTP_USER, password: FTP_PASS, secure: true });
  await client.ensureDir(FTP_DIR);
  if (mode === "down") {
    await client.downloadToDir("public/proyectos-data", FTP_DIR);
    console.log("Descargado servidor -> public/proyectos-data");
  } else {
    await client.uploadFromDir("public/proyectos-data", FTP_DIR);
    console.log("Subido public/proyectos-data -> servidor (seed inicial)");
  }
} catch (e) {
  console.error("Error FTP:", e.message);
  process.exit(1);
} finally {
  client.close();
}
