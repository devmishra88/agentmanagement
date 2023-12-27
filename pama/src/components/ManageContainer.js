import { Container, Grid } from "@mui/material";

function ManageContainer({ ...props }) {

  const { children } = props;
  return (
      <Container maxWidth="lg">
        <Grid container mt={1} mb={8} spacing={1}>
            {children}
        </Grid>
      </Container>
  );
}

export default ManageContainer;
